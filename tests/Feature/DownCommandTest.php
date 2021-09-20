<?php

it('can stop the stack', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('down')
        ->assertExitCode(0);
});

it('can destroy stack data', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('down --destroy')
        ->expectsConfirmation('Are you sure you want to destroy all service data?', 'yes')
        ->assertExitCode(0);
});
