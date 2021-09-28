<?php

namespace App\Commands;

use App\Command;

class RestartCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'restart {service?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restart a stack service';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($service = $this->argument('service')) {
            $this->task("Restart {$service} service", function () use ($service) {
                $this->services->get($service)->restart();
            });
        } else {
            $this->task("Restart stack services", function () {
                $this->compose->restart();
            });
        }
    }
}
