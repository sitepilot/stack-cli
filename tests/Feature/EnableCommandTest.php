<?php

it('can enable the MySQL service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable mysql')
        ->assertExitCode(0);

    $this->assertConfig('mysql.enabled', true);

    $this->assertEnv('STACK_MYSQL_PASSWORD');

    $this->assertEnv('STACK_MYSQL_ROOT_PASSWORD');
});

it('can enable the PhpMyAdmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable phpmyadmin')
        ->assertExitCode(0);

    $this->assertConfig('phpmyadmin.enabled', true);

    $this->assertConfig('proxy.routes.phpmyadmin.path', '/svc/phpmyadmin');
});

it('can enable the Mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable mailhog')
        ->assertExitCode(0);

    $this->assertConfig('mailhog.enabled', true);

    $this->assertConfig('proxy.routes.mailhog.path', '/svc/mailhog');
});
