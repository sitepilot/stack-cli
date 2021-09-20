<?php

namespace App\Services;

use App\Service;

class PhpMyAdminService extends Service
{
    protected string $name = 'phpmyadmin';

    protected array $defaults = [
        'enabled' => false,
        'image' => 'phpmyadmin',
        'tag' => '5.1',
        'uploadLimit' => '2G'
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string'],
        'uploadLimit' => ['required', 'ends_with:M,G']
    ];

    public function init(): void
    {
        $this->publishConfig([
            'proxy.routes.phpmyadmin' => [
                'path' => '/svc/phpmyadmin',
                'url' => "http://{$this->name()}:80"
            ]
        ]);

        $this->publishViews([
            'phpmyadmin' => $this->composeFile()
        ]);
    }

    public function disable(): void
    {
        parent::disable();

        $this->removeConfig([
            'proxy.routes.phpmyadmin'
        ]);
    }
}
