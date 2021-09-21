<?php

namespace App\Commands;

use App\Stack;
use App\Command;
use App\Services\SiteService;
use App\Traits\SiteBackupTrait;

class SiteBackupCommand extends Command
{
    use SiteBackupTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:backup {name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Backup a site';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$backupService = $this->service('backup', ['config', 'enabled', 'running'])) return 2;

        $name = $this->argument('name');

        if ('all' == $name) {
            $sites = Stack::sites();
        } else {
            $sites = [$this->service($name)];
        }

        /** @var SiteService[] $sites */
        if (!count($sites)) {
            return 1;
        }

        foreach ($sites as $siteService) {
            $this->task("[{$siteService->name()}] Initialize backup repository", function () use ($backupService, $siteService) {
                $command = ['exec', '-u', $backupService->config()['user'], '-T', $backupService->name(), 'restic', '-r', $this->backupRepo($backupService, $siteService), 'init'];

                $this->compose($command)->run();
            });

            $this->task("[{$siteService->name()}] Backup site data", function () use ($backupService, $siteService) {
                $command = ['exec', '-u', $backupService->config()['user'], '-T', $backupService->name(), 'restic', '-r', $this->backupRepo($backupService, $siteService), '--tag', $siteService->name(), 'backup', $this->backupPath($siteService)];

                $this->compose($command)->setTimeout(900)->mustRun();
            });
        }
    }
}
