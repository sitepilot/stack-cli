<?php

namespace App\Services\Redis;

use App\Service;

class RedisService extends Service
{
    protected array $defaults = [
        'name' => 'redis',
        'enabled' => false,
        'image' => 'redis',
        'tag' => '6.2'
    ];
}
