<?php

namespace Tests;

use App\Stack;
use Illuminate\Support\Arr;
use Illuminate\Support\Env;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    function assertConfig($key, $value = null)
    {
        $config = Stack::config();

        assertTrue(Arr::has($config, $key), "Failed asserting that configuration key [$key] exists.");

        if ($value) {
            assertEquals($value, Arr::get($config, $key));
        }
    }

    function assertConfigNotHasKey($key)
    {
        $config = Stack::config();

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
