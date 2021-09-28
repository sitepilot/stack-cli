<?php

namespace App\Services\Phpmyadmin;

use App\Service;
use App\Services\Caddy\CaddyService;

class PhpMyAdminService extends Service
{
    private CaddyService $caddy;

    protected array $defaults = [
        'name' => 'phpmyadmin',
        'enabled' => false,
        'image' => 'phpmyadmin',
        'tag' => '5.1',
        'uploadLimit' => '2G'
    ];

    protected array $rules = [
        'uploadLimit' => ['required', 'ends_with:M,G']
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->caddy = resolve(CaddyService::class);
    }

    public function init(): void
    {
        parent::init();

        $this->caddy->enable();

        $this->caddy->setConfig([
            'routes.phpmyadmin' => [
                'path' => '/svc/phpmyadmin',
                'url' => "http://{$this->name()}:80"
            ]
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        $this->caddy->unsetConfig([
            'routes.phpmyadmin'
        ]);
    }

    public function environment(): array
    {
        return [
            'PMA_HOST' => "mysql",
            'PMA_ABSOLUTE_URI' => "/svc/phpmyadmin",
            'UPLOAD_LIMIT' => $this->get('uploadLimit')
        ];
    }
}
