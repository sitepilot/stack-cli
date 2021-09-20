<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

abstract class Service
{
    /**
     * Name of the service.
     *
     * @var string
     */
    protected string $name;

    /**
     * Configuration namespace.
     *
     * @var string
     */
    protected string $namespace = '';

    /**
     * Display name of the service.
     *
     * @var string
     */
    protected string $displayName = '';

    /**
     * The default configuration.
     *
     * @var array
     */
    protected array $defaults = [];

    /**
     * The validation rules.
     *
     * @var array
     */
    protected array $rules = [];

    /**
     * Custom configuration.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Initialize service.
     *
     * @return void
     */
    public function init(): void
    {
        //
    }

    /**
     * Return the service name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Return the service display name.
     *
     * @return string
     */
    public function displayName(): string
    {
        return !empty($this->displayName) ? $this->displayName : ucfirst($this->name);
    }

    /**
     * Return the service namespace.
     *
     * @return string
     */
    public function namespace(): string
    {
        return $this->namespace;
    }

    /**
     * Return the service shell.
     *
     * @return string
     */
    public function shell(): string
    {
        return $this->config()['shell'] ?? 'bash';
    }

    /**
     * Return the service user.
     *
     * @return string
     */
    public function user(): string
    {
        return $this->config()['user'] ?? 'root';
    }

    /**
     * Return the reload command.
     *
     * @return array|null
     */
    public function reloadCommand(): ?array
    {
        return $this->config()['commands']['reload'] ?? null;
    }

    /**
     * Get configuration key.
     *
     * @param string $key
     * @return string
     */
    public function key($key): string
    {
        if ($this->namespace()) {
            $namespace = $this->namespace() . '.';
        }

        return ($namespace ?? '') . $this->name() . '.' . $key;
    }

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->rules as $key => $value) {
            $rules[$this->key($key)] = $value;
        }

        return $rules;
    }

    /**
     * Validate service configuration.
     *
     * @return \Illuminate\contracts\VAlidation\Validator
     */
    public function validator(): \Illuminate\contracts\VAlidation\Validator
    {
        return Validator::make(array_merge(['name' => $this->name], $this->config(true)), $this->rules, config('validation'));
    }

    /**
     * Return service configuration.
     *
     * @return array
     */
    public function config(bool $replaceEnv = true): array
    {
        $config = Stack::config($replaceEnv);

        if (count($this->config)) {
            $config = $this->config;
        } elseif ($this->namespace()) {
            $config = $config[$this->namespace()][$this->name()] ?? [];
        } else {
            $config = $config[$this->name()] ?? [];
        }

        $config['stack_managed'] = "Managed by Stack (" . date('Y-m-d H:i:s') . ")";

        if ($replaceEnv) {
            $defaults = Stack::replaceEnvInArray($this->defaults);
        } else {
            $defaults = $this->defaults;
        }

        return $this->merge($defaults, $config);
    }

    /**
     * Set custom config for validation and initialization.
     *
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Prefix array keys with the service's namespace.
     *
     * @param array $array
     * @return void
     */
    public function prefixArray(array $array)
    {
        $return = array();

        foreach ($array as $key => $value) {
            $return[$this->key($key)] = $value;
        }

        return $return;
    }

    /**
     * Enable the service.
     *
     * @return void
     */
    public function enable(): void
    {
        $this->publishConfig([
            $this->key('enabled') => true
        ], true);

        $this->init();
    }

    /**
     * Disable the service.
     *
     * @return void
     */
    public function disable(): void
    {
        if (Arr::get(Stack::config(), $this->key('enabled'))) {
            $this->publishConfig([
                $this->key('enabled') => false
            ], true);
        }

        File::delete([
            $this->composeFile()
        ]);
    }

    /**
     * Check if service is enabled.
     *
     * @return boolean
     */
    public function enabled(): bool
    {
        if (Arr::get($this->config(), 'enabled', false)) {
            return true;
        }

        return false;
    }

    /**
     * Publish service configuration.
     *
     * @param array $items
     * @param boolean $force
     * @return void
     */
    public function publishConfig(array $items, bool $force = false): void
    {
        Stack::writeConfig(
            array_merge($items, $this->prefixArray($this->config)),
            $force
        );
    }

    /**
     * Publish environment configuration.
     *
     * @param array $items
     * @param boolean $force
     * @return void
     */
    public function publishEnv(array $items, bool $force = false): void
    {
        Stack::writeEnv($items, $force);
    }

    /**
     * Publish service views.
     *
     * @param array $files
     * @return void
     */
    public function publishViews(array $files): void
    {
        foreach ($files as $view => $file) {
            $dir = dirname($file);

            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            File::put($file, view($view, $this->config(), ['service' => $this])->render());
        }
    }

    /**
     * Create service directories.
     *
     * @param array $files
     * @return void
     */
    public function publishDirs(array $directories): void
    {
        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }
    }

    /**
     * Remove service configuration.
     *
     * @param array $items
     * @return void
     */
    public function removeConfig(array $items): void
    {
        Stack::removeConfig($items);
    }

    /**
     * Merge arrays.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function merge(array &$array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (
                is_array($value) &&
                isset($merged[$key]) && is_array($merged[$key]) &&
                count(array_filter(array_keys($value), 'is_string')) > 0
            ) {
                $merged[$key] = $this->merge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * Return compose file path.
     *
     * @return string
     */
    public function composeFile(): string
    {
        return stack_config_path("{$this->name()}.yml");
    }
}
