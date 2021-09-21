<?php

it('can reload stack services', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload')
        ->assertExitCode(0);
});

it('can reload a vhost service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload test-vhost')
        ->assertExitCode(0);
});
