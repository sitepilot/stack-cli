<?php

namespace App\Commands;

use App\Command;
use Illuminate\Support\Str;
use App\Services\MysqlService;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseCreateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'db:create {name} {--user=} {--password=}';

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
        $name = $this->argument('name');

        /** @var MysqlService $service */
        if (!$service = $this->service('mysql', ['config', 'enabled', 'running'])) {
            return 1;
        }

        $user = $this->option('user') ?: $name;
        $password = $this->option('password') ?: Str::random(18);

        try {
            $this->task("Creating database $name", function () use ($service, $name) {
                $this->compose(
                    $service->queryCommand("CREATE DATABASE `$name`;")
                )->mustRun();
            });

            $this->task("Create database user $user", function () use ($service, $user, $password) {
                $this->compose(
                    $service->queryCommand("CREATE USER '{$user}'@'%' IDENTIFIED BY '{$password}';")
                )->mustRun();
            });

            $this->task("Grant permissions to $user", function () use ($service, $name, $user) {
                $this->compose(
                    $service->queryCommand("GRANT ALL ON `$name`.* TO '{$user}'@'%';")
                )->mustRun();
            });

            $this->line('----------------');
            $this->line("Database: $name");
            $this->line("Username: $user");
            $this->line("Password: $password");
            $this->line('----------------');
        } catch (ProcessFailedException $e) {
            $this->error(trim($e->getProcess()->getErrorOutput()));
            return 1;
        }
    }
}
