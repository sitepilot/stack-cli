<?php

namespace App\Services\Caddy;

use App\Service;

class CaddyService extends Service
{
    protected array $defaults = [
        'name' => 'caddy',
        'enabled' => false,
        'image' => 'caddy',
        'tag' => '2.4.3-alpine',
        'shell' => 'sh',
        'workdir' => '/etc/caddy',
        'ports' => [
            'http' => 80,
            'https' => 443
        ],
        'routes' => [],
        'commands' => [
            'reload' => ['caddy', 'reload', '--config', '/etc/caddy/Caddyfile']
        ],
        'backup' => [
            'volume' => 'caddy'
        ]
    ];

    protected array $rules = [
        'routes' => ['nullable', 'array'],
        'routes.*.path' => ['nullable', 'string'],
        'routes.*.url' => ['nullable', 'url']
    ];

    public function init(): void
    {
        parent::init();

        $this->setEnv([
            'STACK_CADDY_PORTS_HTTP' => 80,
            'STACK_CADDY_PORTS_HTTPS' => 443
        ]);

        $this->publishDirs([
            $this->configPath('vhosts')
        ]);

        $this->publishViews([
            'caddy' => $this->configPath('caddy.conf'),
        ]);
    }

    public function ports(): array
    {
        $ports = array();

        if ($this->get('ports.http')) {
            $ports[$this->get('ports.http')] = 80;
        }

        if ($this->get('ports.https')) {
            $ports[$this->get('ports.https')] = 443;
        }

        return $ports;
    }

    public function volumes(): array
    {
        return [
            'caddy' => '/data',
            $this->configPath('vhosts') => '/etc/caddy/vhosts:ro',
            $this->configPath('caddy.conf') => '/etc/caddy/Caddyfile:ro'
        ];
    }
}
