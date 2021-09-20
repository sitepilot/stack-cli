<?php

it('can start the stack', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('up')
        ->assertExitCode(0);
});
