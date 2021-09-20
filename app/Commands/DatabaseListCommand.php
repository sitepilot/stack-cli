<?php

namespace App\Commands;

use App\Command;
use App\Services\MysqlService;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DatabaseListCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'db:list {--format=}';

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
        /** @var MysqlService $service */
        if (!$service = $this->service('mysql', ['config', 'enabled', 'running'])) {
            return 1;
        }

        try {
            $process = $this->compose($service->queryCommand("SHOW DATABASES;"));

            $process->mustRun();

            $output = array_filter(explode("\n", $process->getOutput()), function ($value) {
                if (!empty($value) && !in_array($value, ['mysql', 'sys', 'performance_schema', 'information_schema'])) {
                    return true;
                }

                return false;
            });

            if ('json' == $this->option('format')) {
                $this->line(json_encode($output));
            } else {
                $this->line(implode("\n", $output));
            }
        } catch (ProcessFailedException $e) {
            $this->error(trim($e->getProcess()->getErrorOutput()));
            return 1;
        }
    }
}
