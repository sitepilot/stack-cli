<?php

return [
    'sites' => [],

    'services' => [
        App\Services\Mysql\MysqlService::class,
        App\Services\Mailhog\MailhogService::class,
        App\Services\Phpmyadmin\PhpMyAdminService::class,
        App\Services\Redis\RedisService::class,
        App\Services\Lshttpd\LshttpdService::class,
        App\Services\Caddy\CaddyService::class,
        App\Services\Backup\BackupService::class
    ]
];
