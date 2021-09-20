<?php

it('can reload stack services', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('reload')
        ->assertExitCode(0);
});
