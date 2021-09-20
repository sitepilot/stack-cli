<?php

it('can list stack services', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('services')
        ->assertExitCode(0);
});
