<?php

it('can build stack images', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('build')
        ->assertExitCode(0);
});
