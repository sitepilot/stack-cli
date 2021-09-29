<?php

use App\Services\Backup\BackupService;

it('can enable the phpmyadmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable phpmyadmin')
        ->assertExitCode(0);

    $this->assertConfig('phpmyadmin.enabled', true);

    $this->assertConfig('caddy.routes.phpmyadmin.path', '/.stack/phpmyadmin');
});

it('can start the phpmyadmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up phpmyadmin')
        ->assertExitCode(0);
});

it('can display the phpmyadmin service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config phpmyadmin')
        ->assertExitCode(0);
});

it('can display the phpmyadmin service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs phpmyadmin')
        ->assertExitCode(0);
});

it('can restart the phpmyadmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart phpmyadmin')
        ->assertExitCode(0);
});

it('can disable the phpmyadmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable phpmyadmin')
        ->assertExitCode(0);

    $this->assertConfig('phpmyadmin.enabled', false);

    $this->assertConfigNotHasKey('caddy.routes.phpmyadmin');
});
