<?php

namespace App\Services\Mysql\Commands;

use App\Command;
use App\Services\Mysql\MysqlService;

class MysqlDropCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mysql:drop {name} {--user=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Drop a MySQL database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var MysqlService $mysql */
        $mysql = $this->services->get('mysql');

        $name = $this->argument('name');
        $user = $this->option('user') ?: $name;

        $this->task("Drop user $user", function () use ($mysql, $user) {
            $mysql->dropUser($user);
        });

        $this->task("Drop database $name", function () use ($mysql, $name) {
            $mysql->dropDatabase($name);
        });
    }
}
