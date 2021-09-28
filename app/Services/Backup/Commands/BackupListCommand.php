<?php

namespace App\Services\Backup\Commands;

use App\Command;
use App\Services\Backup\BackupService;

class BackupListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'backup:list {service} {--format=table}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List service backups';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var BackupService $backupService */
        $backupService = $this->services->get('backup');

        $backups = $backupService->getBackups(
            $this->services->get(
                $this->argument('service')
            )
        );

        if ('json' == $this->option('format')) {
            $this->line(json_encode($backups));
        } else {
            $this->table(
                ['ID', 'Time', 'Tags', 'Paths'],
                $backups
            );
        }
    }
}
