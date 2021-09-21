<?php

namespace App\Commands;

use App\Command;
use App\Traits\SiteBackupTrait;

class SiteRestoreCommand extends Command
{
    use SiteBackupTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:restore {name} {backup-id}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restore a site';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$siteService = $this->service($this->argument('name'))) return 1;

        if (!$backupService = $this->service('backup', ['config', 'enabled', 'running'])) return 2;

        $this->task("[{$siteService->name()}] Restore site backup", function () use ($backupService, $siteService) {
            $command = ['exec', '-u', $backupService->config()['user'], '-T', $backupService->name(), 'restic', '-r', $this->backupRepo($backupService, $siteService), 'restore', $this->argument('backup-id'), '--target', '/'];

            $this->compose($command)->setTimeout(900)->mustRun();
        });
    }
}
