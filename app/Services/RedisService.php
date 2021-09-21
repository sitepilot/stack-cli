<?php

namespace App\Services;

use App\Service;

class RedisService extends Service
{
    protected string $name = 'redis';

    protected string $displayName = 'Redis';

    protected array $defaults = [
        'enabled' => false,
        'image' => 'redis',
        'tag' => '6.2'
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string']
    ];

    public function init(): void
    {
        $this->publishViews([
            'redis' => $this->composeFile()
        ]);
    }
}
