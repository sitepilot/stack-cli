<?php

namespace App\Services\Site;

use App\Service;
use App\Services\Caddy\CaddyService;
use Illuminate\Support\Facades\File;
use App\Services\Lshttpd\LshttpdService;

class SiteService extends Service
{
    private CaddyService $caddy;

    private LshttpdService $lshttpd;

    protected array $defaults = [
        'name' => null,
        'namespace' => 'sites',
        'enabled' => true,
        'image' => 'ghcr.io/sitepilot/runtime',
        'tag' => '8.0',
        'user' => 'runtime',
        'domains' => [],
        'ssl' => [
            'email' => null
        ],
        'php' => [
            'timezone' => 'Europe/Amsterdam',
            'uploadSize' => '32M',
            'memoryLimit' => '256M',
            'maxInputVars' => 3000,
            'opcacheMemory' => 128
        ],
        'smtp' => [
            'host' => 'mailhog',
            'port' => 1025,
            'tls' => false,
            'from' => 'hello@stack.test',
            'username' => null,
            'password' => null
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

    public function __construct(string $name, array $config = [])
    {
        $this->set('name', $name);

        parent::__construct($config);

        $this->caddy = resolve(CaddyService::class);
        $this->lshttpd = resolve(LshttpdService::class);
    }

    public function init(): void
    {
        parent::init();

        $this->caddy->enable();
        $this->lshttpd->enable();

        $this->publishDirs([
            $this->dataPath("logs"),
            $this->dataPath("public")
        ]);

        $this->publishViews([
            'php' => $this->configPath("php.ini"),
            'msmtp' => $this->configPath("msmtp.conf"),
            'caddy-vhost' => $this->caddy->configPath("vhosts/{$this->name()}.conf", 'caddy'),
            'lshttpd-vhost' => $this->lshttpd->configPath("vhosts/{$this->name()}.conf", 'lshttpd'),
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        File::delete([
            $this->caddy->configPath("vhosts/{$this->name()}.conf", 'caddy'),
            $this->lshttpd->configPath("vhosts/{$this->name()}.conf", 'lshttpd')
        ]);
    }

    public function workdir(): string
    {
        return "/opt/stack/sites/{$this->name()}/public";
    }

    public function environment(): array
    {
        return [
            'RUNTIME_USER_ID' => $this->uid(),
            'RUNTIME_USER_HOME' => "/opt/stack/sites/{$this->name()}"
        ];
    }

    public function volumes(): array
    {
        return [
            $this->configPath("msmtp.conf") => '/etc/msmtprc:ro',
            $this->dataPath() => "/opt/stack/sites/{$this->name()}",
            $this->configPath("php.ini") => "/usr/local/lsws/lsphp80/etc/php/8.0/mods-available/10-stack.ini:ro"
        ];
    }

    public function backupVolume(): string
    {
        return $this->dataPath("public");
    }
}
