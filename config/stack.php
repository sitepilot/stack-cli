<?php

return [
    'services' => [
        new \App\Services\MysqlService,
        new \App\Services\MailhogService,
        new \App\Services\PhpMyAdminService,
        new \App\Services\RedisService,
        new \App\Services\LshttpdService,
        new \App\Services\CaddyService,
    ]
];
