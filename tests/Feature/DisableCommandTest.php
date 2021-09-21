<?php

it('can disable the MySQL service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable mysql')
        ->assertExitCode(0);

    $this->assertConfig('mysql.enabled', false);
});

it('can disable the Redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable redis')
        ->assertExitCode(0);

    $this->assertConfig('redis.enabled', false);
});

it('can disable the PhpMyAdmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable phpmyadmin')
        ->assertExitCode(0);

    $this->assertConfig('phpmyadmin.enabled', false);

    $this->assertConfigNotHasKey('proxy.routes.phpmyadmin');
});

it('can disable the Mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('disable mailhog')
        ->assertExitCode(0);

    $this->assertConfig('mailhog.enabled', false);

    $this->assertConfigNotHasKey('proxy.routes.mailhog');
});
