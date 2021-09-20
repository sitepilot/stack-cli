<?php

it('can display service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs --limit=10')
        ->assertExitCode(0);
});

it('can display the proxy service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs proxy --limit=1')
        ->assertExitCode(0);
});

it('can display the web service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs web --limit=2')
        ->assertExitCode(0);
});

it('can display the MySQL service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs mysql --limit=2')
        ->assertExitCode(0);
});

it('can display the PhpMyAdmin service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs phpmyadmin --limit=2')
        ->assertExitCode(0);
});

it('can display the Mailhog service logs', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs mailhog --limit=2')
        ->assertExitCode(0);
});

it('can\'t display logs for an unknown service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('logs fake-service')
        ->expectsOutput('Unknown service: fake-service')
        ->assertExitCode(1);
});
