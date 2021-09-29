<?php

namespace App\Services\Mysql\Commands;

use App\Command;
use App\Services\Mysql\MysqlService;

class MysqlRestoreCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mysql:restore {database} {backup-id}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restore a MySQL database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $database = $this->argument('database');

        /** @var MysqlService $mysql */
        $mysql = $this->services->get('mysql');

        /** @var BackupService $backup */
        $backup = $this->services->get('backup');

        $this->task("[{$database}] Run pre-restore commands", function () use ($mysql) {
            $mysql->preRestoreCmd();
        });

        $this->task("[{$database}] Restore service data", function () use ($backup, $mysql) {
            $backup->restoreBackup($mysql, $this->argument('backup-id'));
        });

        $this->task("[{$database}] Run post-restore commands", function () use ($mysql, $database) {
            $mysql->postRestoreCmd($database);
        });
    }
}
