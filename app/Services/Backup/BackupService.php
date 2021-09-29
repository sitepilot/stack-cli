<?php

namespace App\Services\Backup;

use App\Service;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BackupService extends Service
{
    protected array $defaults = [
        'name' => 'backup',
        'enabled' => false,
        'image' => 'ghcr.io/sitepilot/backup',
        'tag' => 'latest',
        'user' => 'root',
        'workdir' => '/opt/stack/services',
        'strategy' => 'local',
        'password' => null,
        's3' => [
            'key' => null,
            'secret' => null,
            'bucket' => null,
        ]
    ];

    protected array $rules = [
        'strategy' => ['required', 'in:local,s3'],
        'password' => ['required', 'string', 'min:8'],
        's3.key' => ['required_if:strategy,s3', 'nullable', 'string'],
        's3.secret' => ['required_if:strategy,s3', 'nullable', 'string'],
        's3.bucket' => ['required_if:strategy,s3', 'nullable', 'string']
    ];

    public function init(): void
    {
        parent::init();

        $this->setEnv([
            'STACK_BACKUP_PASSWORD' => Str::random(18)
        ]);

        if ('local' == $this->get('strategy')) {
            $this->publishDirs([
                $this->dataPath()
            ]);
        }
    }

    public function volumes(): array
    {
        $volumes = array();

        if ('local' == $this->get('strategy')) {
            $volumes[$this->dataPath()] = '/opt/stack/services/backup';
        }

        foreach ($this->services->all() as $service) {
            if ($service->backupVolume()) {
                $volumes[$service->backupVolume()] = "/opt/stack/services/{$service->name()}";
            }
        }

        return $volumes;
    }

    public function environment(): array
    {
        $environment = [
            'RUNTIME_USER_ID' => $this->uid(),
            'RESTIC_PASSWORD' => '${STACK_BACKUP_PASSWORD:?}'
        ];

        if ('s3' == $this->get('strategy')) {
            $environment['AWS_ACCESS_KEY_ID'] = '${STACK_BACKUP_S3_KEY:?}';
            $environment['AWS_SECRET_ACCESS_KEY'] = '${STACK_BACKUP_S3_SECRET:?}';
        }

        return $environment;
    }

    public function backupRepo(Service $service): string
    {
        if (!$service->backupVolume()) {
            abort(1, "The {$service->name()} service is not configured for backups.");
        }

        if ($this->get('strategy', 'local') == 's3') {
            return 's3:' . $this->get('s3.bucket') . '/' . $service->name();
        } else {
            return '/opt/stack/services/backup/' . $service->name();
        }
    }

    public function initBackup(Service $service): string
    {
        return $this->exec(['restic', '-r', $this->backupRepo($service), 'init']);
    }

    public function runBackup(Service $service): string
    {
        return $this->exec(['restic', '-r', $this->backupRepo($service), '--tag', $service->name(), 'backup', "/opt/stack/services/{$service->name()}"], false, 900);
    }

    public function restoreBackup(Service $service, string $id): string
    {
        $output = $this->exec(['restic', '-r', $this->backupRepo($service), 'restore', $id, '--target', "/"]);

        return $output;
    }

    public function getBackups(Service $service): Collection
    {
        return collect(json_decode($this->exec(['restic', '-r', $this->backupRepo($service), 'snapshots', '--json'])))
            ->map(function ($backup) {
                return [
                    'id' => $backup->short_id,
                    'time' => Carbon::createFromFormat('Y-m-d\TH:i:s.uuT', $backup->time, 'UTC')->format('Y-m-d H:i:s'),
                    'tags' => implode(',', $backup->tags),
                    'paths' => implode(',', $backup->paths)
                ];
            })->values();
    }
}
