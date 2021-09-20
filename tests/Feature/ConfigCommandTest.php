<?php

use Illuminate\Support\Facades\Artisan;

it('can display stack configuration', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config')
        ->assertExitCode(0);
});

it('can display stack configration in json format', function () {
    /** @var Tests\TestCase $this */
    Artisan::call('config', ['--format' => 'json']);
    $this->assertJson(Artisan::output());
});

it('can display service configuration', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('config mysql')
        ->assertExitCode(0);
});

it('can display service configration in json format', function () {
    /** @var Tests\TestCase $this */
    Artisan::call('config', ['service' => 'mysql', '--format' => 'json']);
    $this->assertJson(Artisan::output());
});
