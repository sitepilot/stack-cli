<?php

namespace App\Commands;

use App\Command;

class EnableCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'enable {service}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Enable a service';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $service = $this->argument('service');

        $this->task("Enable {$service} service", function () use ($service) {
            $this->services->get($service)->enable();
        });
    }
}
