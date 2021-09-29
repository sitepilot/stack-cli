<?php

use App\Services\Backup\BackupService;

it('can enable the mysql service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable mysql')
        ->assertExitCode(0);

    $this->assertConfig('mysql.enabled', true);

    $this->assertEnv('STACK_MYSQL_PASSWORD');

    $this->assertEnv('STACK_MYSQL_ROOT_PASSWORD');
});

it('can start the mysql service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up mysql')
        ->assertExitCode(0);
});

it('can display the mysql service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config mysql')
        ->assertExitCode(0);
});

it('can display the mysql service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs mysql')
        ->assertExitCode(0);
});

it('can backup and restore the mysql service', function () {
    resolve(BackupService::class)->enable()->up();

    /** @var Tests\TestCase $this */
    $this->artisan('backup mysql')
        ->assertExitCode(0);

    $this->artisan('backup:restore mysql latest')
        ->assertExitCode(0);
});

it('can create a mysql database', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('mysql:create test-database')
        ->assertExitCode(0);
});

it('can list mysql databases', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('mysql:list')
        ->assertExitCode(0);
});

it('can backup and restore a mysql database backup', function () {
    resolve(BackupService::class)->enable()->up();

    /** @var Tests\TestCase $this */
    $this->artisan('backup mysql')
        ->assertExitCode(0);

    $this->artisan('mysql:restore test-database latest')
        ->assertExitCode(0);
});

it('can drop a mysql database', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('mysql:drop test-database')
        ->assertExitCode(0);
});

it('can restart the mysql service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart mysql')
        ->assertExitCode(0);
});

it('can disable the mysql service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable mysql')
        ->assertExitCode(0);

    $this->assertConfig('mysql.enabled', false);
});
