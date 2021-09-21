<?php

it('can disable the mysql service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable mysql')
        ->assertExitCode(0);

    $this->assertConfig('mysql.enabled', false);
});

it('can disable the redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable redis')
        ->assertExitCode(0);

    $this->assertConfig('redis.enabled', false);
});

it('can disable the phpmyadmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable phpmyadmin')
        ->assertExitCode(0);

    $this->assertConfig('phpmyadmin.enabled', false);

    $this->assertConfigNotHasKey('proxy.routes.phpmyadmin');
});

it('can disable the mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable mailhog')
        ->assertExitCode(0);

    $this->assertConfig('mailhog.enabled', false);

    $this->assertConfigNotHasKey('proxy.routes.mailhog');
});

it('can disable the backup service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable backup')
        ->assertExitCode(0);

    $this->assertConfig('backup.enabled', false);
});
