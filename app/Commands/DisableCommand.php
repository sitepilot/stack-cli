<?php

namespace App\Commands;

use App\Stack;
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
        $name =  $this->argument('service');

        if (!$service = $this->service($name)) {
            return 1;
        }

        $service->disable();

        $this->info("Successfully disabled: {$service->displayName()}, don't forget to reload the stack!");
    }
}
