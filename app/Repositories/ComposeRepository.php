<?php

namespace App\Repositories;

use App\Service;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ComposeRepository
{
    /**
     * Compose services cache.
     *
     * @var array
     */
    private array $composeServices = [];

    /**
     * The configuration repository.
     *
     * @var ConfigRepository
     */
    protected ConfigRepository $config;

    /**
     * The services repository.
     *
     * @var ServicesRepository
     */
    protected ServicesRepository $services;

    /**
     * Creates a new repository instance.
     *
     * @return void
     */
    public function __construct(
        ConfigRepository $config,
        ServicesRepository $service
    ) {
        $this->config = $config;
        $this->services = $service;
    }

    /**
     * Run Docker Compose command.
     *
     * @param array $cmd
     * @param bool $tty
     * @param int $timeout
     * @return string
     */
    public function run(array $command, $tty = false, $timeout = 60): string
    {
        $composeCmd = ['docker-compose', '-p', $this->config->projectName()];

        if (!count($this->composeServices)) {
            $this->composeServices = $this->services->all(
                ['enabled' => true]
            );
        }

        foreach ($this->composeServices as $service) {
            if (!File::exists($service->composeFile())) {
                abort(1, "Not all services are initialized, run `stack init` to initialize the stack and try again.");
            }

            array_push($composeCmd, '-f', $service->composeFile());
        }

        if (File::exists($this->config->projectPath('stack.override.yml'))) {
            array_push($composeCmd, '-f', $this->config->projectPath('stack.override.yml'));
        }

        if (File::exists($this->config->envFile())) {
            array_push($composeCmd, '--env-file', $this->config->envFile());
        }

        if (!in_array('-f', $composeCmd)) {
            abort(1, "No services available, enable a service with `stack enable <service>` and try again.");
        }

        $process = (new Process(array_merge($composeCmd, $command)))
            ->setTty($tty)
            ->setTimeout($timeout);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            abort(1, $e->getMessage());
        }

        return $process->getOutput();
    }

    /**
     * Start containers.
     *
     * @return string
     */
    public function up(?Service $service = null): string
    {
        $command = ['up', '-d', '--remove-orphans'];

        if ($service) {
            array_push($command, $service->name());
        }

        return $this->run($command);
    }

    /**
     * Pull container(s).
     *
     * @param string|null $serviceName
     * @return string
     */
    public function restart(?Service $service = null): string
    {
        $command = ['restart'];

        if ($service) {
            array_push($command, $service->name());
        }

        return $this->run($command, false, 900);
    }

    /**
     * Stop containers.
     *
     * @param boolean $destroy
     * @return string
     */
    public function down(bool $destroy = false): string
    {
        $command = ['down', '--remove-orphans'];

        if ($destroy) {
            array_push($command, '--volumes');
        }

        return $this->run($command);
    }

    /**
     * Get container logs.
     *
     * @param Service|null $serviceName
     * @param integer $limit
     * @param boolean $follow
     * @return string
     */
    public function logs(?Service $service = null, int $limit = 25, bool $follow = false): string
    {
        $command = ['logs', '--tail', $limit];

        if ($follow) {
            array_push($command, '--follow');
        }

        if ($service) {
            array_push($command, $service->name());
        }

        return $this->run($command, $follow, $follow ? 0 : 60);
    }

    /**
     * Pull container image(s).
     *
     * @param Service|null $service
     * @return string
     */
    public function pull(?Service $service = null): string
    {
        $command = ['pull'];

        if ($service) {
            array_push($command, $service->name());
        }

        return $this->run($command, false, 900);
    }

    /**
     * Execute command in container.
     *
     * @param Service $service
     * @param array $exec
     * @param boolean $tty
     * @param integer $timeout
     * @return string
     */
    public function exec(Service $service, array $exec, bool $tty = false, int $timeout = 60): string
    {
        $command = ['exec', '-u', $service->get('user', 'root')];

        if (!$tty) {
            array_push($command, '-T');
        }

        array_push($command, $service->name());

        return $this->run(array_merge($command, $exec), $tty, $timeout);
    }

    /**
     * List running services.
     *
     * @return string
     */
    public function list(): string
    {
        return $this->run(['ps', '-a']);
    }
}
