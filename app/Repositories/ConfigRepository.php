<?php

namespace App\Repositories;

use Dotenv\Dotenv;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class ConfigRepository
{
    /**
     * Returns the current user ID.
     *
     * @return integer
     */
    public function uid(): int
    {
        return exec('id -u');
    }

    /**
     * Get the path to the stack project configuration.
     *
     * @return string
     */
    public function path(string $path): string
    {
        return $this->projectPath(env('STACK_DIR', '.stack') . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }

    /**
     * Get the path to the base of the project.
     *
     * @param string $path
     * @return string
     */
    public function projectPath($path = ''): string
    {
        return env('STACK_PATH', getcwd()) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get path to the .env file.
     *
     * @return string
     */
    public function envFile(): string
    {
        return $this->projectPath('.env');
    }

    /**
     * Get path to the stack.yml file.
     *
     * @return string
     */
    public function stackFile(): string
    {
        return $this->projectPath('stack.yml');
    }

    /**
     * Get the stack project name.
     *
     * @return string
     */
    function projectName(): string
    {
        return basename($this->projectPath());
    }

    /**
     * Load stack environment.
     *
     * @return void
     */
    public function loadEnv(): void
    {
        if (file_exists($this->envFile())) {
            Dotenv::createMutable(dirname($this->envFile()), basename($this->envFile()))->load();
        }
    }

    /**
     * Get the stack configuration.
     *
     * @return array
     */
    public function all(): array
    {
        if (file_exists($this->stackFile())) {
            $config = Yaml::parseFile($this->stackFile());

            if (is_array($config)) {
                return $this->replaceEnv($config);
            }
        }

        return [];
    }

    /**
     * Get an item from the config using "dot" notation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * Get the config as YAML.
     *
     * @param array|null $config
     * @return string
     */
    public function toYaml(array $config = null): string
    {
        return "---" . PHP_EOL . trim(preg_replace('/\-\n\s+/', '- ', Yaml::dump($config ? $config : $this->all(), 99, 2)));
    }

    /**
     * Get the config as JSON.
     *
     * @param array|null $config
     * @return string
     */
    public function toJson(array $config = null)
    {
        return json_encode($config ? $config : $this->all());
    }

    /**
     * Write stack configuration.
     *
     * @param array $items
     * @param boolean $force
     * @return void
     */
    public function set(array $items, bool $force = false): void
    {
        $config = $this->all();

        foreach ($items as $key => $value) {
            if (!Arr::get($config, $key, false) || $force) {
                Arr::set($config, $key, $value);
            }
        }

        File::put($this->stackFile(), $this->toYaml($config));
    }

    /**
     * Write to environment file.
     *
     * @param array $items
     * @param boolean $force
     * @return void
     */
    public function setEnv(array $items, $force = false)
    {
        $envFile = $this->envFile();

        if (!File::exists($envFile)) {
            File::put($envFile, "");
        }

        foreach ($items as $key => $value) {
            $pattern = "/^$key=(.*)/m";
            $content = trim(File::get($envFile));
            $value = is_numeric($value) ? $value : "\"$value\"";

            if ($force && preg_match($pattern, $content)) {
                File::put($envFile, preg_replace(
                    $pattern,
                    "$key=$value",
                    $content
                ));
            } elseif (!preg_match($pattern, $content)) {
                File::put($envFile, $content . (strlen($content) > 0 ? "\n" : "") . "$key=$value");
            }
        }

        $this->loadEnv();
    }

    /**
     * Remove stack configuration.
     *
     * @param array $items
     * @return void
     */
    public function unset(array $items): void
    {
        $config = $this->all(false);

        Arr::forget($config, $items);

        File::put($this->stackFile(), $this->toYaml($config));
    }

    /**
     * Replace environment variables in array.
     *
     * @param array $array
     * @return array
     */
    public function replaceEnv(array $array, $prefix = ''): array
    {
        $return = array();

        foreach ($array as $key => $value) {
            $envKey = str_replace('-', '_', Str::upper(($prefix ? $prefix . '_' : '') . $key));

            if (is_array($value)) {
                $return[$key] = $this->replaceEnv($value, $envKey);
            } else {
                $return[$key] = env('STACK_' . $envKey, $value);
            }
        }

        return $return;
    }
}
