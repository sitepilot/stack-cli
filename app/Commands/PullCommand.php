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
        $serviceName = $this->argument('service');

        if ($serviceName && !$service = $this->service($serviceName, ['config', 'enabled'])) {
            return 1;
        }

        $command = ['pull'];

        if ($service ?? null) {
            array_push($command, $service->name());
        }

        $this->task("Pull container images", function () use ($command) {
            $this->compose($command)->mustRun();
        });
    }
}
