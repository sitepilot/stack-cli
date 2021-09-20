<?php

namespace App\Commands;

use App\Stack;
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
        $name =  $this->argument('service');

        if (!$service = $this->service($name)) {
            return 1;
        }

        $service->enable();

        $this->info("Successfully enabled: {$service->displayName()}, don't forget to reload the stack!");
    }
}
