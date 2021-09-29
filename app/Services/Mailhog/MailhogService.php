<?php

namespace App\Services\Mailhog;

use App\Service;
use App\Services\Caddy\CaddyService;

class MailhogService extends Service
{
    private CaddyService $caddy;

    protected array $defaults = [
        'name' => 'mailhog',
        'enabled' => false,
        'image' => 'mailhog/mailhog',
        'tag' => 'latest',
        'path' => '/.stack/mailhog'
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
            'routes.mailhog' => [
                'path' => $this->get('path'),
                'url' => "http://{$this->name()}:8025"
            ]
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        $this->caddy->unsetConfig([
            'routes.mailhog'
        ]);
    }
}
