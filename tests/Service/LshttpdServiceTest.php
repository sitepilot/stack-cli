<?php

use App\Services\Backup\BackupService;

it('can enable the lshttpd service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable lshttpd')
        ->assertExitCode(0);

    $this->assertConfig('lshttpd.enabled', true);

    $this->assertEnv('STACK_LSHTTPD_PASSWORD');
});

it('can start the lshttpd service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up lshttpd')
        ->assertExitCode(0);
});

it('can display the lshttpd service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config lshttpd')
        ->assertExitCode(0);
});

it('can display the lshttpd service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs lshttpd')
        ->assertExitCode(0);
});

it('can reload the lshttpd service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload lshttpd')
        ->assertExitCode(0);
});

it('can restart the lshttpd service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart lshttpd')
        ->assertExitCode(0);
});

it('can disable the lshttpd service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable lshttpd')
        ->assertExitCode(0);

    $this->assertConfig('lshttpd.enabled', false);
});
