<?php

namespace App\Services;

use App\Stack;
use App\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LshttpdService extends Service
{
    protected string $name = 'web';

    protected string $user = 'runtime';

    protected array $defaults = [
        'enabled' => true,
        'image' => 'ghcr.io/sitepilot/lshttpd',
        'tag' => 'latest',
        'user' => 'runtime',
        'uid' => null,
        'ports' => [
            'http' => '${STACK_WEB_HTTP_PORT}',
            'https' => '${STACK_WEB_HTTPS_PORT}',
            'admin' => '${STACK_WEB_ADMIN_PORT}'
        ],
        'username' => '${STACK_WEB_ADMIN_USERNAME:-admin}',
        'password' => '${STACK_WEB_ADMIN_PASSWORD}',
        'email' => '${STACK_WEB_ADMIN_EMAIL:-hello@stack.local}',
        'commands' => [
            'reload' => ['reload']
        ]
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string'],
        'ports.http' => ['nullable', 'numeric'],
        'ports.https' => ['nullable', 'numeric'],
        'ports.admin' => ['nullable', 'numeric'],
        'username' => ['required', 'min:3'],
        'password' => ['required', 'min:8']
    ];

    public function __construct()
    {
        Arr::set($this->defaults, 'uid', Stack::uid());
    }

    public function init(): void
    {
        $this->publishEnv([
            'STACK_WEB_ADMIN_PASSWORD' => Str::random(18)
        ]);

        $this->publishDirs([
            stack_config_path('config/lshttpd/vhosts')
        ]);

        $this->publishViews([
            'lshttpd' => $this->composeFile(),
            'lshttpd-conf' => stack_config_path('config/lshttpd/lshttpd.conf'),
            'lshttpd-admin' => stack_config_path('config/lshttpd/admin.conf'),
        ]);
    }
}
