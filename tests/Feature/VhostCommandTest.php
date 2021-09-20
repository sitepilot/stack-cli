<?php

it('can create a vhost', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('vhost:create test-vhost --domains=* --tag=8.0')
        ->assertExitCode(0);

    $this->assertConfig('vhosts.test-vhost.tag', '8.0');
});
