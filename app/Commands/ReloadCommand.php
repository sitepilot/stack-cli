<?php

namespace App\Commands;

use App\Command;

class ReloadCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'reload {service?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Gracefully reload stack services';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($service = $this->argument('service')) {
            $this->task("Reload {$service} service", function () use ($service) {
                $this->services->get($service)->reload();
            });
        } else {
            $this->task("Initialize configuration", function() {
                $this->services->init();
            });

            $this->task("Update service containers", function () {
                $this->compose->up();
            });

            foreach ($this->services->all([
                'enabled' => true,
                'commands.reload' => '*'
            ]) as $service) {
                $this->task("Reload {$service->name()} service", function () use ($service) {
                    $service->reload();
                });
            }
        }
    }
}
