<?php

namespace App\Commands;

use App\Command;
use App\Services\MysqlService;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseDropCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'db:drop {name} {--user=}';

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
        $name = $this->argument('name');

        /** @var MysqlService $service */
        if (!$service = $this->service('mysql', ['config', 'enabled', 'running'])) {
            return 1;
        }

        $user = $this->option('user') ?: $name;

        try {
            $this->task("Dropping database $name", function () use ($service, $name) {
                $this->compose(
                    $service->queryCommand("DROP DATABASE `$name`;")
                )->mustRun();
            });

            $this->task("Dropping database user $user", function () use ($service, $user) {
                $this->compose(
                    $service->queryCommand("DROP USER `$user`;")
                )->mustRun();
            });
        } catch (ProcessFailedException $e) {
            $this->error(trim($e->getProcess()->getErrorOutput()));
            return 1;
        }
    }
}
