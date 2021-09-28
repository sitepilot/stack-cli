<?php

namespace App\Services\Lshttpd;

use App\Service;
use Illuminate\Support\Str;

class LshttpdService extends Service
{
    protected array $defaults = [
        'name' => 'lshttpd',
        'enabled' => false,
        'image' => 'ghcr.io/sitepilot/lshttpd',
        'tag' => 'latest',
        'user' => 'root',
        'workdir' => '/usr/local/lsws',
        'ports' => [
            'http' => null,
            'https' => null,
            'admin' => null
        ],
        'username' => 'admin',
        'password' => null,
        'email' => 'hello@stack.local',
        'commands' => [
            'reload' => ['reload']
        ]
    ];

    protected array $rules = [
        'ports.http' => ['nullable', 'numeric'],
        'ports.https' => ['nullable', 'numeric'],
        'ports.admin' => ['nullable', 'numeric'],
        'username' => ['required', 'min:3'],
        'password' => ['required', 'min:8']
    ];

    public function init(): void
    {
        parent::init();

        $this->setEnv([
            'STACK_LSHTTPD_PASSWORD' => Str::random(18)
        ]);

        $this->publishDirs([
            $this->configPath('vhosts')
        ]);

        $this->publishViews([
            'admin' => $this->configPath('admin.conf'),
            'lshttpd' => $this->configPath('lshttpd.conf')
        ]);
    }

    public function environment(): array
    {
        return [
            'RUNTIME_USER_ID' => $this->uid(),
            'ADMIN_USERNAME' => $this->get('username'),
            'ADMIN_PASSWORD' => '${STACK_LSHTTPD_PASSWORD:?}'
        ];
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

        if ($this->get('ports.admin')) {
            $ports[$this->get('ports.admin')] = 7080;
        }

        return $ports;
    }

    public function volumes(): array
    {
        $volumes = [
            $this->configPath('vhosts') => '/usr/local/lsws/conf/vhosts:ro',
            $this->configPath('lshttpd.conf') => '/usr/local/lsws/conf/httpd_config.conf:ro',
            $this->configPath('admin.conf') => '/usr/local/lsws/admin/conf/admin_config.conf:ro'
        ];

        if (count($this->services->sites())) {
            $volumes[$this->dataPath('sites', false)] =  '/opt/stack/sites';
        }

        return $volumes;
    }
}
