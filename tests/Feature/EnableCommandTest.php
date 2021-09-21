<?php

it('can enable the mysql service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable mysql')
        ->assertExitCode(0);

    $this->assertConfig('mysql.enabled', true);

    $this->assertEnv('STACK_MYSQL_PASSWORD');

    $this->assertEnv('STACK_MYSQL_ROOT_PASSWORD');
});

it('can enable the redis service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable redis')
        ->assertExitCode(0);

    $this->assertConfig('redis.enabled', true);
});

it('can enable the phpmyadmin service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable phpmyadmin')
        ->assertExitCode(0);

    $this->assertConfig('phpmyadmin.enabled', true);

    $this->assertConfig('proxy.routes.phpmyadmin.path', '/svc/phpmyadmin');
});

it('can enable the mailhog service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable mailhog')
        ->assertExitCode(0);

    $this->assertConfig('mailhog.enabled', true);

    $this->assertConfig('proxy.routes.mailhog.path', '/svc/mailhog');
});

it('can enable the backup service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('enable backup')
        ->assertExitCode(0);

    $this->assertConfig('backup.enabled', true);

    $this->assertEnv('STACK_BACKUP_STRATEGY');

    $this->assertEnv('STACK_BACKUP_PASSWORD');
});
