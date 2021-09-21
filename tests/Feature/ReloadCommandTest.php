<?php

it('can reload stack services', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload')
        ->assertExitCode(0);
});

it('can reload a site service', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload test-site')
        ->assertExitCode(0);
});
