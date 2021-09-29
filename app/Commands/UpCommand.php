<?php

namespace App\Commands;

use App\Command;

class UpCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'up {service?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start service containers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($service = $this->argument('service')) {
            $this->task("Start {$service} service", function () use ($service) {
                $this->services->get($service)->up();
            });
        } else {
            $this->task("Start stack services", function () {
                $this->compose->up();
            });
        }
    }
}
