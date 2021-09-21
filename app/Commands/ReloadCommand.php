<?php

namespace App\Commands;

use App\Stack;
use App\Command;
use App\Services\VhostService;
use Illuminate\Support\Facades\Artisan;

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
        if (!$this->validate()) {
            return 1;
        }

        $serviceName = $this->argument('service');

        if ($serviceName && !$service = $this->service($serviceName)) {
            return 1;
        }

        $this->task("Initialize configuration", function () {
            $this->init();
        });

        $this->task("Update service containers", function () {
            $this->compose(['up', '-d', '--remove-orphans'])->mustRun();
        });

        if ($service ?? null) {
            $this->task("Restart {$service->name()} service", function () use ($service) {
                $this->compose(['restart', $service->name()])->mustRun();
            });
        }

        foreach (Stack::services(true, true) as $service) {
            $cmd = $service->reloadCommand();

            if ($cmd) {
                $this->task("Reload {$service->name()} " . ($service instanceof VhostService ? 'vhost' : 'service'), function () use ($service, $cmd) {
                    $this->compose(array_merge(['exec', '-T', $service->name()], $cmd))->mustRun();
                });
            }
        }
    }
}
