<?php

namespace App\Commands;

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
        $service = $this->services->get($this->argument('service'));

        $service->exec([$service->get('shell', 'bash')], Process::isTtySupported(), 0);
    }
}
