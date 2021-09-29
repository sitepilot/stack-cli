<?php

namespace App\Services\Mysql\Commands;

use App\Command;
use Illuminate\Support\Str;
use App\Services\Mysql\MysqlService;

class MysqlCreateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mysql:create {name} {--user=} {--password=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new MySQL database';

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
        $pass = $this->option('password') ?: Str::random(18);

        $this->task("Creating database $name", function () use ($mysql, $name) {
            $mysql->createDatabase($name);
        });

        $this->task("Create database user $user", function () use ($mysql, $user, $pass) {
            $mysql->createUser($user, $pass);
        });

        $this->task("Grant permissions to $user", function () use ($mysql, $user, $name) {
            $mysql->grantUser($user, $name);
        });

        $this->info("\n----------------");
        $this->info("Database: $name");
        $this->info("Username: $user");
        $this->info("Password: $pass");
        $this->info("----------------\n");
    }
}
