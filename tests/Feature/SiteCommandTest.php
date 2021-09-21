<?php

it('can create a site', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('site:create test-site --domains=* --tag=8.0')
        ->assertExitCode(0);

    $this->assertConfig('sites.test-site.tag', '8.0');
});

it('can backup a site', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('site:backup test-site')
        ->assertExitCode(0);
});

it('can list site backups', function () {
    $this->artisan('site:backups test-site')
        ->assertExitCode(0);
});

it('can restore a site backup', function () {
    $this->artisan('site:restore test-site latest')
        ->assertExitCode(0);
});
