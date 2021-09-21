<?php

namespace App\Services;

use App\Stack;
use App\Service;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BackupService extends Service
{
    protected string $name = 'backup';

    protected array $defaults = [
        'enabled' => false,
        'image' => 'ghcr.io/sitepilot/backup',
        'tag' => 'latest',
        'user' => 'runtime',
        'uid' => null,
        'strategy' => '${STACK_BACKUP_STRATEGY}',
        'password' => '${STACK_BACKUP_PASSWORD}',
        's3' => [
            'key' => '${STACK_BACKUP_S3_KEY}',
            'secret' => '${STACK_BACKUP_S3_SECRET}',
            'bucket' => '${STACK_BACKUP_S3_BUCKET}',
        ]
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string'],
        'strategy' => ['required', 'in:local,s3'],
        'password' => ['required', 'string', 'min:8'],
        's3.key' => ['required_if:strategy,s3', 'nullable', 'string'],
        's3.secret' => ['required_if:strategy,s3', 'nullable', 'string'],
        's3.bucket' => ['required_if:strategy,s3', 'nullable', 'string']
    ];

    public function __construct()
    {
        Arr::set($this->defaults, 'uid', Stack::uid());
    }

    public function init(): void
    {
        $this->publishEnv([
            'STACK_BACKUP_STRATEGY' => 'local',
            'STACK_BACKUP_PASSWORD' => Str::random(18)
        ]);

        $this->publishViews([
            'backup' => $this->composeFile(),
        ]);

        $this->publishDirs([
            stack_config_path('backups')
        ]);
    }
}
