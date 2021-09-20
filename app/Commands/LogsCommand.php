<?php

namespace App\Commands;

use App\Stack;
use App\Command;
use Symfony\Component\Process\Process;

class LogsCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs {service?}
            {--f|follow : Follow service container logs.}
            {--l|limit=25 : Number of lines to show from the end of the logs.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show service container logs';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('service');

        if ($name && !$service = $this->service($name, ['enabled', 'running'])) {
            return 1;
        }

        $cmd = ['logs', '--tail', $this->option('limit')];

        if ($this->option('follow')) {
            array_push($cmd, '--follow');
        }

        if ($service ?? null) {
            array_push($cmd, $service->name());
        }

        $process = $this->compose($cmd)
            ->setTimeout(0)
            ->setTty(
                $this->option('follow') && Process::isTtySupported()
            )->mustRun();

        $this->line($process->getOutput());
    }
}
