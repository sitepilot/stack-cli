<?php

namespace App;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Repositories\ConfigRepository;
use Illuminate\Support\Facades\Config;
use App\Repositories\ComposeRepository;
use App\Repositories\ServicesRepository;
use Exception;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

abstract class Service
{
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
     * The custom configuration.
     *
     * @var array
     */
    protected array $publishConfig = [];

    /**
     * The config repository.
     *
     * @var ConfigRepository
     */
    protected ConfigRepository $config;

    /**
     * The compose repository.
     *
     * @var ComposeRepository
     */
    protected ComposeRepository $compose;

    /**
     * The compose repository.
     *
     * @var ServicesRepository
     */
    protected ServicesRepository $services;

    /**
     * Creates a new service instance.
     *
     * @return void
     */
    public function __construct(array $publishConfig = [])
    {
        $this->config = resolve(ConfigRepository::class);
        $this->compose = resolve(ComposeRepository::class);
        $this->services = resolve(ServicesRepository::class);

        foreach ($publishConfig as $key => $value) {
            Arr::set($this->publishConfig, $key, $value);
        }

        $this->loadCommandsAndViews();
    }

    /**
     * Load service commands and views.
     *
     * @return void
     */
    private function loadCommandsAndViews(): void
    {
        $serviceDir = dirname(
            (new \ReflectionClass($this))->getFileName()
        ) . DIRECTORY_SEPARATOR;

        [$viewDir, $cmdDir] = [
            $serviceDir . 'Views',
            $serviceDir . 'Commands'
        ];

        if (!in_array($cmdDir, Config::get('commands.paths'))) {
            Config::set(
                'commands.paths',
                array_merge(Config::get('commands.paths'), [$cmdDir])
            );
        }

        View::addNamespace(
            ($this->namespace() ? Str::singular($this->namespace()) : $this->name()),
            $viewDir
        );
    }

    /**
     * Initialize service.
     *
     * @return void
     */
    public function init(): void
    {
        $this->setConfig(
            $this->publishConfig
        );

        $this->publishViews([
            'service' => $this->composeFile()
        ], false);
    }

    /**
     * Return the service name.
     *
     * @return string
     */
    public function name(): string
    {
        $name = Arr::get($this->defaults, 'name');

        if (!$name = Arr::get($this->defaults, 'name')) {
            abort(1, "Invalid name for service " . get_class($this));
        }

        return $name;
    }

    /**
     * Return the service namespace.
     *
     * @return string|null
     */
    public function namespace(): ?string
    {
        return Arr::get($this->defaults, 'namespace', null);
    }

    /**
     * Return the service user ID.
     *
     * @return int
     */
    public function uid(): int
    {
        return $this->config->uid();
    }

    /**
     * Get configuration key.
     *
     * @param string $key
     * @return string
     */
    public function key(string $key = '', string $delimiter = '.'): string
    {
        if ($this->namespace()) {
            $namespace = $this->namespace() . $delimiter;
        }

        return ($namespace ?? '') . $this->name() . ($key ? $delimiter . $key : '');
    }

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        $defaultRules = [
            'name' => ['required', 'string', 'min:3'],
            'namespace' => ['string', 'min:3'],
            'enabled' => ['required', 'boolean'],
            'image' => ['required', 'string'],
            'tag' => ['required', 'string'],
            'workdir' => ['nullable', 'string'],
            'ports' => ['nullable', 'array'],
            'volumes' => ['nullable', 'array'],
            'environment' => ['nullable', 'array'],
            'commands' => ['nullable', 'array'],
            'commands.*.reload' => ['nullable', 'array']
        ];

        return array_merge($defaultRules, $this->rules);
    }

    /**
     * Get the service configuration.
     *
     * @return array
     */
    public function config(): array
    {
        $config = array_merge(
            $this->config->get($this->key(), []),
            $this->publishConfig
        );

        $defaults = $this->defaults;

        if ($this->namespace()) {
            $defaults = $this->config->replaceEnv(
                $defaults,
                Str::singular($this->namespace())
            );
        }

        $defaults = $this->config->replaceEnv(
            $defaults,
            $this->key('', '_')
        );

        return $this->merge($defaults, $config);
    }

    /**
     * Get an item from the service configuration.
     *
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->config(), $key, $default);
    }

    /**
     * Update default config.
     *
     * @param string|null $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value): self
    {
        Arr::set($this->defaults, $key, $value);

        return $this;
    }

    /**
     * Validate service configuration.
     *
     * @return \Illuminate\contracts\VAlidation\Validator
     */
    public function validator(): \Illuminate\contracts\VAlidation\Validator
    {
        return Validator::make($this->config(), $this->rules(), config('validation'));
    }

    /**
     * Validate service configuration.
     *
     * @param array $rules
     * @return self
     */
    public function validate(array $rules = ['config', 'enabled', 'running']): self
    {
        $validationErrors = ["The {$this->name()} service configuration is invalid:"];

        foreach ($this->validator()->errors()->all() as $error) {
            $validationErrors[] = "[{$this->name()}] $error";
        }

        if (count($validationErrors) > 1) {
            abort(1, implode(PHP_EOL, $validationErrors));
        }

        if (in_array('enabled', $rules) && !$this->enabled()) {
            abort(1, "The {$this->name()} service is not enabled.");
        }

        if (in_array('running', $rules) && !$this->containerId()) {
            abort(1, "The {$this->name()} service is not running.");
        }

        return $this;
    }

    /**
     * Enable the service.
     *
     * @return self
     */
    public function enable(): self
    {
        $this->config->set([
            $this->key('enabled') => true
        ], true);

        $this->init();

        return $this;
    }

    /**
     * Check if service is enabled.
     *
     * @return boolean
     */
    public function enabled(): bool
    {
        return $this->get('enabled', false);
    }

    /**
     * Disable the service.
     *
     * @return void
     */
    public function disable(): void
    {
        if ($this->config->get($this->key('enabled'))) {
            $this->config->set([
                $this->key('enabled') => false
            ], true);
        }

        File::deleteDirectory(
            $this->configPath()
        );
    }

    /**
     * Check if service is disabled.
     *
     * @return boolean
     */
    public function disabled(): bool
    {
        return !$this->get('enabled', false);
    }

    /**
     * Publish service configuration.
     *
     * @param array $config
     * @param boolean $force
     * @return void
     */
    public function setConfig(array $config, bool $force = false)
    {
        $newConfig = array();

        foreach ($config as $key => $value) {
            $newConfig[$this->key($key)] = $value;
        }

        $this->config->set($newConfig, $force);
    }

    /**
     * Remove service configuration.
     *
     * @param array $config
     * @return void
     */
    public function unsetConfig(array $config): void
    {
        $removeConfig = array();

        foreach ($config as $value) {
            $removeConfig[] = $this->key($value);
        }

        $this->config->unset($removeConfig);
    }

    /**
     * Publish service environment.
     *
     * @param array $environment
     * @param bool $force
     * @return void
     */
    public function setEnv(array $environment, bool $force = false): void
    {
        $this->config->setEnv($environment, $force);
    }

    /**
     * Publish service directories.
     *
     * @param array $directories
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
     * Publish service views.
     *
     * @param array $files
     * @param bool $namespaced
     * @return void
     */
    public function publishViews(array $files, bool $namespaced = true): void
    {
        foreach ($files as $view => $file) {
            $dir = dirname($file);

            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            if ($namespaced) {
                $view = ($this->namespace() ? Str::singular($this->namespace()) : $this->name()) . '::' . $view;
            }

            File::put($file, view($view, $this->config(), [
                'service' => $this,
                'stack_managed' => 'Managed by Stack (' . date('Y-m-d H:i:s') . ')',
                'organization' => env('STACK_ORGANIZATION', 'Stack')
            ])->render());
        }
    }

    /**
     * Get compose file path.
     *
     * @return string
     */
    public function composeFile(): string
    {
        return $this->configPath("{$this->name()}.yml");
    }

    /**
     * Get service configuration path.
     *
     * @param string $path
     * @return string
     */
    public function configPath(string $path = ''): string
    {
        $path = $this->name() . ($path ? DIRECTORY_SEPARATOR . $path : $path);

        if ($this->namespace()) {
            $path = $this->namespace() . DIRECTORY_SEPARATOR . $path;
        }

        return $this->config->path('config' . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Get service local data path.
     *
     * @param string $path
     * @return string
     */
    public function dataPath(string $path = '', bool $prefix = true): string
    {
        if ($prefix) {
            $path = $this->name() . ($path ? DIRECTORY_SEPARATOR . $path : $path);

            if ($this->namespace()) {
                $path = $this->namespace() . DIRECTORY_SEPARATOR . $path;
            }
        }

        return $this->config->path('data' . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Get the service image.
     *
     * @return string
     */
    public function image(): string
    {
        return $this->get('image', '') . ':' . $this->get('tag');
    }

    /**
     * Get the service working directory.
     *
     * @return string
     */
    public function workdir(): string
    {
        return $this->get('workdir', '/');
    }

    /**
     * Get the service environment.
     *
     * @return array
     */
    public function environment(): array
    {
        return $this->get('environment', []);
    }

    /**
     * Get the service ports.
     *
     * @return array
     */
    public function ports(): array
    {
        return $this->get('ports', []);
    }

    /**
     * Get the service volumes.
     *
     * @return array
     */
    public function volumes(): array
    {
        return $this->get('volumes', []);
    }

    /**
     * Get the service named volumes.
     *
     * @return array
     */
    public function namedVolumes(): array
    {
        $volumes = array();

        foreach ($this->volumes() as $mount => $volume) {
            $match = array();

            if (preg_match('/^([A-Za-z\-]+)/', $mount, $match)) {
                $volumes[] = reset($match);
            }
        }

        return $volumes;
    }

    /**
     * Execute command in service container.
     *
     * @param array $cmd
     * @return string
     */
    public function exec(array $command, bool $tty = false, int $timeout = 60): string
    {
        $this->validate(['enabled', 'running']);

        return $this->compose->exec($this, $command, $tty, $timeout);
    }

    /**
     * Start service container.
     *
     * @return string
     */
    public function up(): string
    {
        $this->validate(['enabled']);

        return $this->compose->up($this);
    }

    /**
     * Restart service container.
     *
     * @return string
     */
    public function restart(): string
    {
        $this->validate(['enabled', 'running']);

        return $this->compose->restart($this);
    }

    /**
     * Reload service container.
     *
     * @return mixed
     */
    public function reload()
    {
        if (!$command = $this->get('commands.reload')) {
            abort(1, "No reload command specified for the {$this->name()} service.");
        }

        return $this->exec($command);
    }

    /**
     * Get service logs.
     *
     * @return string
     */
    public function logs(...$args): string
    {
        $this->validate(['enabled', 'running']);

        return $this->compose->logs($this, ...$args);
    }

    /**
     * Pull service container image.
     *
     * @return string
     */
    public function pull(): string
    {
        $this->validate(['enabled']);

        return $this->compose->pull($this);
    }

    /**
     * Get the service container ID.
     *
     * @return string|null
     */
    public function containerId(): ?string
    {
        try {
            return $this->compose->run(['ps', '-q', $this->name()]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the service backup volume.
     *
     * @return string|null
     */
    public function backupVolume(): ?string
    {
        return $this->get('backup.volume', null);
    }

    /**
     * Run pre-backup commands.
     *
     * @return void
     */
    public function preBackupCmd(): void
    {
        //
    }

    /**
     * Run post-backup commands.
     *
     * @return void
     */
    public function postBackupCmd(): void
    {
        //
    }

    /**
     * Run pre-restore commands.
     *
     * @return void
     */
    public function preRestoreCmd(): void
    {
        //
    }

    /**
     * Run post-restore commands.
     *
     * @return void
     */
    public function postRestoreCmd(): void
    {
        //
    }

    /**
     * Merge 2 arrays.
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
}
