<?php

use App\Services\Backup\BackupService;

it('can enable the redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable redis')
        ->assertExitCode(0);

    $this->assertConfig('redis.enabled', true);
});

it('can start the redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up redis')
        ->assertExitCode(0);
});

it('can display the redis service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config redis')
        ->assertExitCode(0);
});

it('can display the redis service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs redis')
        ->assertExitCode(0);
});

it('can restart the redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart redis')
        ->assertExitCode(0);
});

it('can disable the redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable redis')
        ->assertExitCode(0);

    $this->assertConfig('redis.enabled', false);
});
