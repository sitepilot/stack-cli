<?php

namespace App;

use App\Services\VhostService;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use LaravelZero\Framework\Commands\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * Get service container ID.
     *
     * @param Service $service
     * @return string
     */
    public function serviceId(Service $service): string
    {
        $process = $this->compose(['ps', '-q', $service->name()])
            ->setTty(false);

        $process->run();

        return $process->getOutput();
    }

    /**
     * Initialize stack configuration.
     *
     * @return void
     */
    public function init(): void
    {
        foreach (Stack::services(false, true) as $service) {
            if ($service->enabled()) {
                $service->init();
            } else {
                $service->disable();
            }
        }
    }

    /**
     * Validate stack configuration.
     *
     * @return void
     */
    public function validate(?string $service = null)
    {
        $errors = array();

        if (
            !File::exists(stack_project_path('stack.yml'))
            || !File::exists(stack_project_path('.env'))
        ) {
            $this->error('Stack not initalized, run `stack init` to initialize the stack first.');
            return false;
        }

        foreach (Stack::services(true, true) as $service) {
            foreach ($service->validator()->errors()->all() as $error) {
                $errors[] = '[' . ($service instanceof VhostService ? 'vhosts.' : '') . $service->name()  . '] ' . $error;
            }
        }

        if (count($errors) > 0) {
            $this->error("Configuration validation failed, please check your stack.yml file.");

            foreach ($errors as $error) {
                $this->error($error);
            }

            return false;
        }

        return true;
    }

    /**
     * Return and validate service.
     *
     * @param string $name
     * @param boolean $validate
     * @return Service|null
     */
    public function service(string $name, array $validationRules = []): ?Service
    {
        $service = Stack::service($name, false, true);

        if (!$service) {
            $this->error("Unknown service: $name");
            return null;
        }

        if (in_array('config', $validationRules) && count($service->validator()->errors()->all())) {
            $this->error("Service validation failed, please check your stack.yml file.");

            foreach ($service->validator()->errors()->all() as $error) {
                $this->error("[{$service->name()}] $error");
            }

            return null;
        }

        if (in_array('enabled', $validationRules) && !$service->enabled()) {
            $this->error("{$service->displayName()} service is not enabled.");
            return null;
        }

        if (in_array('running', $validationRules) && !$this->serviceContainerId($service)) {
            $this->error("{$service->displayName()} service is not running, enable service and reload the stack.");
            return null;
        }

        return $service;
    }

    /**
     * Return service container ID.
     *
     * @param Service $service
     * @return string|null
     */
    public function serviceContainerId(Service $service): ?string
    {
        $process = $this->compose(['ps', '-q', $service->name()])->setTty(false);

        $process->run();

        return $process->getOutput();
    }

    /**
     * Returns a Docker Compose process.
     *
     * @param array $cmd
     * @return Process
     */
    public function compose(array $cmd): Process
    {
        $composeCmd = ['docker-compose', '-p', stack_project_name()];

        foreach (Stack::services(true, true) as $service) {
            if (File::exists($service->composeFile())) {
                array_push($composeCmd, '-f', $service->composeFile());
            }
        }

        if (File::exists(stack_project_path('stack.override.yml'))) {
            array_push($composeCmd, '-f', stack_project_path('stack.override.yml'));
        }

        if (File::exists(stack_project_path('.env'))) {
            array_push($composeCmd, '--env-file', stack_project_path('.env'));
        }

        return (new Process(array_merge($composeCmd, $cmd)))
            ->setTty(false);
    }
}
