<?php

namespace App\Repositories;

use App\Service;
use App\Services\Site\SiteService;
use Illuminate\Support\Facades\File;

class ServicesRepository
{
    /**
     * The configuration repository.
     *
     * @var ConfigRepository
     */
    protected ConfigRepository $config;

    /**
     * Creates a new repository instance.
     *
     * @return void
     */
    public function __construct(
        ConfigRepository $config
    ) {
        $this->config = $config;
    }

    /**
     * Get all registered services.
     *
     * @return Service[]
     */
    public function all(array $filter = []): array
    {
        return array_filter(array_merge(config('stack.services'), $this->sites()), function (Service $service) use ($filter) {
            if (count($filter)) {
                foreach ($filter as $key => $expected) {
                    $value = $service->get($key);
                    if (
                        $expected == '*' && empty($value)
                        || $expected != '*' && $value != $expected
                    ) {
                        return false;
                    }
                }
            }

            return true;
        });
    }

    /**
     * Get all registered sites.
     *
     * @return array
     */
    public function sites(): array
    {
        return config('stack.sites');
    }

    /**
     * Get a service by name.
     *
     * @param string $name
     * @return Service|null
     */
    public function get(string $name, $type = null): Service
    {
        if (!$type) {
            $type = Service::class;
        }

        foreach ($this->all() as $service) {
            if ($service->name() == $name && $service instanceof $type) {
                return $service;
            }
        }

        abort(1, "The $name service doesn't exist.");
    }

    /**
     * Validate stack configuration.
     *
     * @param array $rules
     * @return self
     */
    public function validate(): self
    {
        if (!File::exists($this->config->stackFile()) || !File::exists($this->config->envFile())) {
            abort(1, 'Stack not initalized, run `stack init` to initialize the stack first.');
        }

        $validationErrors = ["Stack configuration validation failed, please check your stack.yml file."];

        foreach ($this->all() as $service) {
            if ($service->enabled()) {
                foreach ($service->validator()->errors()->all() as $error) {
                    $validationErrors[] = '[' . ($service instanceof SiteService ? 'sites.' : '') . $service->name()  . '] ' . $error;
                }
            }
        }

        if (count($validationErrors) > 1) {
            abort(1, implode(PHP_EOL, $validationErrors));
        }

        return $this;
    }

    /**
     * Initialize stack services.
     *
     * @return void
     */
    public function init()
    {
        $this->validate();

        foreach ($this->all() as $service) {
            if ($service->enabled()) {
                $service->init();
            } else {
               $service->disable();
            }
        }
    }
}
