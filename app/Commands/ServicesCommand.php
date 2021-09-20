<?php

namespace App\Commands;

use App\Command;

class ServicesCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'services';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show running service containers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = $this->compose(['ps', '-a']);

        $process->mustRun();

        $this->line($process->getOutput());
    }
}
