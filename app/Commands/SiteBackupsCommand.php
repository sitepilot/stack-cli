<?php

namespace App\Commands;

use App\Command;
use App\Traits\SiteBackupTrait;
use Illuminate\Support\Carbon;

class SiteBackupsCommand extends Command
{
    use SiteBackupTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:backups {name} {--format=table}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List site backups';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!$siteService = $this->service($this->argument('name'))) return 1;

        if (!$backupService = $this->service('backup', ['config', 'enabled', 'running'])) return 2;

        $process = $this->compose(['exec', '-u', $backupService->config()['user'], '-T', $backupService->name(), 'restic', '-r', $this->backupRepo($backupService, $siteService), 'snapshots', '--json']);

        $process->mustRun();

        $rows = array();
        foreach (json_decode($process->getOutput()) as $backup) {
            $rows[] = [
                'id' => $backup->short_id,
                'time' => Carbon::createFromFormat('Y-m-d\TH:i:s.uuT', $backup->time, 'UTC')->format('Y-m-d H:i:s'),
                'tags' => implode(',', $backup->tags),
                'paths' => implode(',', $backup->paths)
            ];
        }

        if ('json' == $this->option('format')) {
            $this->line(json_encode($rows));
        } else {
            $this->table(
                ['ID', 'Time', 'Tags', 'Paths'],
                $rows
            );
        }
    }
}
