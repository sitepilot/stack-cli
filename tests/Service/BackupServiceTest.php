<?php

use App\Services\Backup\BackupService;

it("can enable the backup service", function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable backup')
        ->assertExitCode(0);

    $this->assertConfig('backup.enabled', true);

    $this->assertEnv('STACK_BACKUP_PASSWORD');
});

it('can start the backup service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up backup')
        ->assertExitCode(0);
});

it('can display the backup service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config backup')
        ->assertExitCode(0);
});

it('can display the backup service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs backup')
        ->assertExitCode(0);
});

it('can restart the backup service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart backup')
        ->assertExitCode(0);
});

it('can disable the backup service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable backup')
        ->assertExitCode(0);

    $this->assertConfig('backup.enabled', false);
});
