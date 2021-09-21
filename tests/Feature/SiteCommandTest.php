<?php

it('can create a site', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('site:create test-site --domains=* --tag=8.0')
        ->assertExitCode(0);

    $this->assertConfig('sites.test-site.tag', '8.0');
});
