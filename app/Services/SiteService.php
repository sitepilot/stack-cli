<?php

namespace App\Services;

use App\Stack;
use App\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class SiteService extends Service
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
            'email' => '${STACK_SITE_SSL_EMAIL}'
        ],
        'php' => [
            'timezone' => '${STACK_SITE_PHP_TIMEZONE:-Europe/Amsterdam}',
            'uploadSize' => '${STACK_SITE_PHP_UPLOAD_SIZE:-32M}',
            'memoryLimit' => '${STACK_SITE_PHP_MEMORY_LIMIT:-256M}',
            'maxInputVars' => '${STACK_SITE_PHP_MAX_INPUT_SIZE:-3000}',
            'opcacheMemory' => '${STACK_SITE_PHP_OPCACHE_MEMORY:-128}'
        ],
        'smtp' => [
            'host' => '${STACK_SITE_SMTP_HOST:-mailhog}',
            'port' => '${STACK_SITE_SMTP_PORT:-1025}',
            'tls' => '${STACK_SITE_SMTP_TLS:-false}',
            'from' => '${STACK_SITE_SMTP_FROM:-hello@stack.test}',
            'username' => '${STACK_SITE_SMTP_USERNAME}',
            'password' => '${STACK_SITE_SMTP_PASSWORD}'
        ],
        'denyFiles' => [
            'xmlrpc.php',
            'wp-trackback.php'
        ],
        'basicAuth' => [
            //[
            //  'path' => '/*',
            //  'users' => [[
            //    'username' => 'stack',
            //    'password' => 'JDJhJDE0JEY2MlhpMDBCbmozd0p2UnB5R3YzNC42WjlqZ2ZuN3NuUUZoMHlraXcwcU1sRldmN2o0eXZL'
            //   ]]
            //]
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
        'smtp.from' => ['required', 'email'],
        'denyFiles' => ['array'],
        'denyFiles.*' => ['required', 'string'],
        'basicAuth' => ['array'],
        'basicAuth.*.path' => ['required', 'string'],
        'basicAuth.*.users' => ['required', 'array'],
        'basicAuth.*.users.*.username' => ['required', 'string', 'min:3'],
        'basicAuth.*.users.*.password' => ['required', 'string', 'min:12']
    ];

    public function __construct(?string $name)
    {
        $this->namespace = 'sites';

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
            'site' => $this->composeFile(),
            'caddy-site' => stack_config_path("config/caddy/sites/{$this->name()}.conf"),
            'lshttpd-site' => stack_config_path("config/lshttpd/sites/{$this->name()}.conf"),
            'php-site' => stack_project_path("sites/{$this->name()}/config/php.ini"),
            'msmtp-site' => stack_project_path("sites/{$this->name()}/config/msmtp.conf")
        ]);

        $this->publishDirs([
            stack_project_path("sites/{$this->name()}/logs"),
            stack_project_path("sites/{$this->name()}/public")
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        File::delete([
            stack_config_path("config/caddy/sites/{$this->name()}.conf"),
            stack_config_path("config/lshttpd/sites/{$this->name()}.conf")
        ]);
    }

    public function composeFile(): string
    {
        return stack_project_path("sites/{$this->name()}/config/service.yml");
    }
}
