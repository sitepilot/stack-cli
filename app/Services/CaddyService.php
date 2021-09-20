<?php

namespace App\Services;

use App\Service;

class CaddyService extends Service
{
    protected string $name = 'proxy';

    protected array $defaults = [
        'enabled' => true,
        'image' => 'caddy',
        'tag' => '2.4.3-alpine',
        'shell' => 'sh',
        'ports' => [
            'http' => '${STACK_PROXY_HTTP_PORT:-80}',
            'https' => '${STACK_PROXY_HTTPS_PORT:-443}'
        ],
        'routes' => [],
        'commands' => [
            'reload' => ['caddy', 'reload', '--config', '/etc/caddy/Caddyfile']
        ]
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string', 'in:caddy'],
        'tag' => ['required', 'string'],
        'routes' => ['nullable', 'array'],
        'routes.*.path' => ['nullable', 'string'],
        'routes.*.url' => ['nullable', 'url'],
        'commands' => ['nullable', 'array'],
        'commands.*.reload' => ['nullable', 'array']
    ];

    public function init(): void
    {
        $this->publishEnv([
            'STACK_PROXY_HTTP_PORT' => 80,
            'STACK_PROXY_HTTPS_PORT' => 443
        ]);

        $this->publishDirs([
            stack_config_path('config/caddy/vhosts')
        ]);

        $this->publishViews([
            'caddy' => $this->composeFile(),
            'caddy-conf' => stack_config_path('config/caddy/caddy.conf'),
        ]);
    }
}
