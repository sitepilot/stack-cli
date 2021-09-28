<?php

use App\Services\Backup\BackupService;

it('can create a site', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('site:create test-site --domains=* --tag=8.0')
        ->assertExitCode(0);

    $this->assertConfig('sites.test-site.tag', '8.0');
});

it('can enable the site service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable test-site')
        ->assertExitCode(0);

    $this->assertConfig('sites.test-site.enabled', true);

    $this->assertConfig('sites.test-site.domains.0', '*');
});

it('can start the site service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up test-site')
        ->assertExitCode(0);
});

it('can display the site service config', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config test-site')
        ->assertExitCode(0);
});

it('can display the site service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs test-site')
        ->assertExitCode(0);
});

it('can backup and restore the site service', function () {
    resolve(BackupService::class)->enable()->up();

    /** @var Tests\TestCase $this */
    $this->artisan('backup test-site')
        ->assertExitCode(0);

    $this->artisan('backup:restore test-site latest')
        ->assertExitCode(0);
});

it('can restart the test-site service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('restart test-site')
        ->assertExitCode(0);
});

it('can disable the test-site service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable test-site')
        ->assertExitCode(0);

    $this->assertConfig('sites.test-site.enabled', false);
});
