<?php

use App\Services\Backup\BackupService;

it('can enable the caddy service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable caddy')
        ->assertExitCode(0);

    $this->assertConfig('caddy.enabled', true);

    $this->assertEnv('STACK_CADDY_PORTS_HTTP');

    $this->assertEnv('STACK_CADDY_PORTS_HTTPS');
});

it('can start the caddy service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up caddy')
        ->assertExitCode(0);
});

it('can display the caddy service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config caddy')
        ->assertExitCode(0);
});

it('can display the caddy service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs caddy')
        ->assertExitCode(0);
});

it('can backup and restore the caddy service', function () {
    resolve(BackupService::class)->enable()->up();

    /** @var Tests\TestCase $this */
    $this->artisan('backup caddy')
        ->assertExitCode(0);

    $this->artisan('backup:restore caddy latest')
        ->assertExitCode(0);
});

it('can reload the caddy service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload caddy')
        ->assertExitCode(0);
});

it('can restart the caddy service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart caddy')
        ->assertExitCode(0);
});

it('can disable the caddy service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable caddy')
        ->assertExitCode(0);

    $this->assertConfig('caddy.enabled', false);
});
