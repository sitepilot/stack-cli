<?php

namespace Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use App\Repositories\ConfigRepository;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use LaravelZero\Framework\Exceptions\ConsoleException;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected ConfigRepository $config;

    public function __construct(...$args)
    {
        parent::__construct(...$args);

        $this->config = resolve(ConfigRepository::class);
    }

    function assertConfig($key, $value = null)
    {
        $config = $this->config->all();

        assertTrue(Arr::has($config, $key), "Failed asserting that configuration key [$key] exists.");

        if ($value) {
            assertEquals($value, Arr::get($config, $key));
        }
    }

    function assertConfigNotHasKey($key)
    {
        $config = $this->config->all();

        assertFalse(Arr::has($config, $key), "Failed asserting that configuration key [$key] doesn't exist.");
    }

    function assertEnv($key, $value = null)
    {
        assertNotNull(Env::get($key), "Failed asserting that environment variable [$key] exists.");

        if ($value) {
            assertEquals($value, Env::get($key), "Failed asserting that environment variable [$key] has value [$value].");
        }
    }
}
