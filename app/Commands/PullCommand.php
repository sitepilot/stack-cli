<?php

namespace App\Commands;

use App\Command;

class PullCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'pull {service?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Pull service container images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($service = $this->argument('service')) {
            $this->task("Pull {$service} image", function () use ($service) {
                $this->services->get($service)->pull();
            });
        } else {
            $this->task("Pull container images", function () {
                $this->compose->pull();
            });
        }
    }
}
