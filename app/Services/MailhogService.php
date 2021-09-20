<?php

namespace App\Services;

use App\Service;

class MailhogService extends Service
{
    protected string $name = 'mailhog';

    protected array $defaults = [
        'enabled' => false,
        'image' => 'mailhog/mailhog',
        'tag' => 'latest'
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string']
    ];

    public function init(): void
    {
        $this->publishConfig([
            'proxy.routes.mailhog' => [
                'path' => '/svc/mailhog',
                'url' => "http://{$this->name()}:8025"
            ]
        ]);

        $this->publishViews([
            'mailhog' => $this->composeFile()
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        $this->removeConfig([
            'proxy.routes.mailhog'
        ]);
    }
}
