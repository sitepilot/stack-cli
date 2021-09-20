<?php

use Illuminate\Support\Facades\Artisan;

it('can create a database', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('db:create test-database')
        ->assertExitCode(0);
});

it('can list databases', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('db:list')
        ->assertExitCode(0);
});

it('can list databases in json format', function () {
    /** @var Tests\TestCase $this */
    Artisan::call('db:list', ['--format' => 'json']);
    $this->assertJson(Artisan::output());
});

it('can drop a database', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('db:drop test-database')
        ->assertExitCode(0);
});
