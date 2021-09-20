<?php

namespace App\Commands;

use App\Stack;
use App\Command;
use Symfony\Component\Process\Process;

class ShellCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'shell {service}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start shell to a service container';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('service');

        if (!$service = $this->service($name, ['enabled', 'running'])) {
            return 1;
        }

        $this->compose(['exec', '-u', $service->user(), $service->name(), $service->shell()])
            ->setTimeout(0)
            ->setTty(Process::isTtySupported())
            ->run();
    }
}
