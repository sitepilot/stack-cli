<?php

namespace App\Commands;

use App\Command;

class ConfigCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config {service?} {--format=yaml}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Display service config';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($service = $this->argument('service')) {
            $config = $this->services->get(
                $service
            )->config();
        } else {
            $config = $this->config->all();
        }

        switch ($this->option('format')) {
            case 'json':
                $this->line(
                    $this->config->toJson($config)
                );
                break;
            case 'yaml':
            default:
                $this->line(
                    $this->config->toYaml($config)
                );
                break;
        }
    }
}
