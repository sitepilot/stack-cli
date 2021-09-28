<?php

namespace App\Services\Backup\Commands;

use App\Command;

class BackupRestoreCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {service} {backup-id}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restore a service';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var BackupService $backupService */
        $backupService = $this->services->get('backup');

        $service = $this->services->get(
            $this->argument('service'),
        );

        $this->task("[{$service->name()}] Run pre-restore commands", function () use ($service) {
            $service->preRestoreCmd();
        });

        $this->task("[{$service->name()}] Restore service data", function () use ($backupService, $service) {
            $backupService->restoreBackup($service, $this->argument('backup-id'));
        });

        $this->task("[{$service->name()}] Run post-restore commands", function () use ($service) {
            $service->postRestoreCmd();
        });
    }
}
