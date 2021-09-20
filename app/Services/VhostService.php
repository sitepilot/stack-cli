<?php

namespace App\Services;

use App\Stack;
use App\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class VhostService extends Service
{
    protected string $user = 'runtime';

    protected array $defaults = [
        'enabled' => true,
        'image' => 'ghcr.io/sitepilot/runtime',
        'tag' => '8.0',
        'user' => 'runtime',
        'uid' => null,
        'domains' => [],
        'ssl' => [
            'email' => '${STACK_VHOST_SSL_EMAIL}'
        ],
        'php' => [
            'timezone' => '${STACK_VHOST_PHP_TIMEZONE:-Europe/Amsterdam}',
            'uploadSize' => '${STACK_VHOST_PHP_UPLOAD_SIZE:-32M}',
            'memoryLimit' => '${STACK_VHOST_PHP_MEMORY_LIMIT:-256M}',
            'maxInputVars' => '${STACK_VHOST_PHP_MAX_INPUT_SIZE:-3000}',
            'opcacheMemory' => '${STACK_VHOST_PHP_OPCACHE_MEMORY:-128}'
        ],
        'smtp' => [
            'host' => '${STACK_VHOST_SMTP_HOST:-mailhog}',
            'port' => '${STACK_VHOST_SMTP_PORT:-1025}',
            'tls' => '${STACK_VHOST_SMTP_TLS:-false}',
            'from' => '${STACK_VHOST_SMTP_FROM:-hello@stack.test}',
            'username' => '${STACK_VHOST_SMTP_USERNAME}',
            'password' => '${STACK_VHOST_SMTP_PASSWORD}'
        ]
    ];

    protected array $rules = [
        'name' => ['required', 'string'],
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string'],
        'domains' => ['required', 'array'],
        'ssl.email' => ['nullable', 'email'],
        'php.timezone' => ['required', 'timezone'],
        'php.uploadSize' => ['required', 'ends_with:M,G'],
        'php.memoryLimit' => ['required', 'ends_with:M,G'],
        'php.maxInputVars' => ['required', 'numeric'],
        'php.opcacheMemory' => ['required', 'numeric'],
        'smtp.host' => ['required'],
        'smtp.port' => ['required', 'numeric'],
        'smtp.tls' => ['required', 'boolean'],
        'smtp.from' => ['required', 'email']
    ];

    public function __construct(?string $name)
    {
        $this->namespace = 'vhosts';

        $this->name = $name;

        Arr::set($this->defaults, 'uid', Stack::uid());
    }

    public function init(): void
    {
        $this->publishConfig(array_merge([
            $this->key('tag') => '8.0',
            $this->key('domains') => []
        ]));

        $this->publishViews([
            'vhost' => $this->composeFile(),
            'caddy-vhost' => stack_config_path("config/caddy/vhosts/{$this->name()}.conf"),
            'lshttpd-vhost' => stack_config_path("config/lshttpd/vhosts/{$this->name()}.conf"),
            'php-vhost' => stack_project_path("vhosts/{$this->name()}/config/php.ini"),
            'msmtp-vhost' => stack_project_path("vhosts/{$this->name()}/config/msmtp.conf")
        ]);

        $this->publishDirs([
            stack_project_path("vhosts/{$this->name()}/logs"),
            stack_project_path("vhosts/{$this->name()}/public")
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        File::delete([
            stack_config_path("config/caddy/vhosts/{$this->name()}.conf"),
            stack_config_path("config/lshttpd/vhosts/{$this->name()}.conf")
        ]);
    }

    public function composeFile(): string
    {
        return stack_project_path("vhosts/{$this->name()}/config/service.yml");
    }
}
