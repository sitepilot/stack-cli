<?php

namespace App\Commands;

use App\Command;

class DisableCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'disable {service}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Disable a service';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $service = $this->argument('service');

        $this->task("Disable {$service} service", function () use ($service) {
            $this->services->get($service)->disable();
        });
    }
}
