<?php

it('can initialize the stack', function () {
    /** @var Tests\TestCase $this */
    $this->artisan('init')
        ->assertExitCode(0);

    $this->assertFileExists(stack_project_path('stack.yml'));

    $this->assertFileExists(stack_project_path('.env'));

    $this->assertEnv('STACK_PROXY_HTTP_PORT');

    $this->assertEnv('STACK_PROXY_HTTPS_PORT');

    $this->assertEnv('STACK_WEB_ADMIN_PASSWORD');
});
