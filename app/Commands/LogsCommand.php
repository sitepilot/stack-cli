<?php

namespace App\Commands;

use App\Command;

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
        if ($service = $this->argument('service')) {
            $this->line($this->services->get($service)->logs(
                $this->option('limit'),
                $this->option('follow')
            ));
        } else {
            $this->line($this->compose->logs(
                null,
                $this->option('limit'),
                $this->option('follow')
            ));
        }
    }
}
