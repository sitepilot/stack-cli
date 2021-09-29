<?php

namespace App\Services\Backup\Commands;

use Exception;
use App\Command;
use App\Service;
use App\Services\Backup\BackupService;

class BackupCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'backup {service}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Backup a service';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var BackupService $backupService */
        $backupService = $this->services->get('backup')->validate();

        if ('all' == $this->argument('service')) {
            $services = array_filter($this->services->all(['enabled' => true]), function (Service $service) {
                return $service->backupVolume() ? true : false;
            });
        } else {
            $services = [$this->services->get(
                $this->argument('service'),
            )];
        }

        $errors = array();

        foreach ($services as $service) {
            try {
                $this->task("[{$service->name()}] Validate service", function () use ($service) {
                    $service->validate();
                });

                $this->task("[{$service->name()}] Initialize backup repository", function () use ($backupService, $service) {
                    try {
                        $backupService->initBackup($service);
                    } catch (Exception $e) {
                        // Already initialized
                    }
                });

                $this->task("[{$service->name()}] Run pre-backup commands", function () use ($service) {
                    $service->preBackupCmd();
                });

                $this->task("[{$service->name()}] Backup service data", function () use ($backupService, $service) {
                    $backupService->runBackup($service);
                });

                $this->task("[{$service->name()}] Run post-backup commands", function () use ($service) {
                    $service->postBackupCmd();
                });
            } catch (Exception $e) {
                $errors[] = "{$e->getMessage()}";
            }
        }

        if (count($errors)) {
            abort(1, "Backup failed:" . PHP_EOL . implode(PHP_EOL, $errors));
        }
    }
}
