<?php

namespace App;

use Exception;
use Illuminate\Support\Arr;
use App\Services\VhostService;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;

class Stack
{
    /**
     * Get stack configuration.
     *
     * @return array
     */
    public static function config(bool $replaceEnv = true): array
    {
        if (file_exists(stack_project_path('stack.yml'))) {
            $config = Yaml::parseFile(stack_project_path('stack.yml'));

            if (is_array($config)) {
                if ($replaceEnv) {
                    $config = self::replaceEnvInArray($config);
                }

                return $config;
            }
        }

        return [];
    }

    /**
     * Replace environment variables in array.
     *
     * @param array $array
     * @return array
     */
    public static function replaceEnvInArray(array $array): array
    {
        $return = array();
        $pattern = '/\${(.*)}/';
        $matches = [];

        foreach ($array as $key => $value) {
            if (is_string($value) && preg_match($pattern, $value, $matches)) {
                $explode = preg_split('/:-/', $matches[1]);
                $value = env($explode[0], $explode[1] ?? null);

                if (is_numeric($value)) {
                    $value = (int)$value;
                } elseif ($value === 'true') {
                    $value = true;
                } elseif ($value === 'false') {
                    $value = false;
                }

                $return[$key] = $value;
            } elseif (is_array($value)) {
                $return[$key] = self::replaceEnvInArray($value);
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Get all registered services.
     *
     * @param boolean $enabled
     * @return Service[]
     */
    public static function services($enabled = false, $vhosts = false): array
    {
        $services = config('stack.services');

        if ($enabled) {
            $services = array_filter($services, function ($service) {
                return $service->enabled();
            });
        }

        if ($vhosts) {
            $services = array_merge($services, self::vhosts($enabled));
        }

        return $services;
    }

    /**
     * Get service instance by name.
     *
     * @param string $service
     * @return Service|null
     */
    public static function service(?string $name, $enabled = false, $vhosts = false): ?Service
    {
        foreach (self::services($enabled, $vhosts) as $service) {
            if ($name == $service->name()) {
                return $service;
            }
        }

        return null;
    }

    /**
     * Get all registered vhosts.
     *
     * @param boolean $enabled
     * @return VhostService[]
     */
    public static function vhosts($enabled = false): array
    {
        $vhosts = array();

        foreach (self::config()['vhosts'] ?? [] as $name => $config) {
            $vhosts[] = new VhostService($name);
        }

        if ($enabled) {
            $vhosts = array_filter($vhosts, function ($service) {
                return $service->enabled();
            });
        }

        return $vhosts;
    }

    /**
     * Write to environment file.
     *
     * @param array $items
     * @param boolean $force
     * @return void
     */
    public static function writeEnv(array $items, $force = false)
    {
        $envFile = stack_project_path('.env');

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
    }

    /**
     * Write stack configuration.
     *
     * @param array $items
     * @param boolean $force
     * @return void
     */
    public static function writeConfig(array $items, bool $force = false): void
    {
        $config = self::config(false);

        foreach ($items as $key => $value) {
            if (!Arr::get($config, $key, false) || $force) {
                Arr::set($config, $key, $value);
            }
        }

        File::put(stack_project_path('stack.yml'), "---" . PHP_EOL . Yaml::dump($config, 99, 2));
    }

    /**
     * Remove stack configuration.
     *
     * @param array $items
     * @return void
     */
    public static function removeConfig(array $items): void
    {
        $config = self::config(false);

        Arr::forget($config, $items);

        File::put(stack_project_path('stack.yml'), "---" . PHP_EOL . Yaml::dump($config, 99, 2));
    }
}
