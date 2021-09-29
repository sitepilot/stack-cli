<?php

namespace App\Services\Mysql\Commands;

use App\Command;

class MysqlListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mysql:list {--format=table}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List MySQL databases';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var MysqlService $mysql */
        $mysql = $this->services->get('mysql');

        $databases = $mysql->getDatabases();

        if ('json' == $this->option('format')) {
            $this->line($databases->toJson());
        } else {
            $this->table(
                ['Name'],
                $databases->toArray()
            );
        }
    }
}
