<?php

use App\Services\Backup\BackupService;

it('can enable the mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable mailhog')
        ->assertExitCode(0);

    $this->assertConfig('mailhog.enabled', true);

    $this->assertConfig('caddy.routes.mailhog.path', '/.stack/mailhog');
});

it('can start the mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up mailhog')
        ->assertExitCode(0);
});

it('can display the mailhog service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config mailhog')
        ->assertExitCode(0);
});

it('can display the mailhog service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs mailhog')
        ->assertExitCode(0);
});

it('can restart the mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart mailhog')
        ->assertExitCode(0);
});

it('can disable the mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable mailhog')
        ->assertExitCode(0);

    $this->assertConfig('mailhog.enabled', false);
});
