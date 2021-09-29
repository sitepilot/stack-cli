<?php

namespace App\Commands;

use App\Command;

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init {service?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Initialize stack configuration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($service = $this->argument('service')) {
            $this->task("Initialize {$service} configuration", function () use ($service) {
                $this->services->get($service)->init();
            });
        } else {
            $this->task("Initialize stack configuration", function () {
                $this->services->init();
            });
        }
    }
}
