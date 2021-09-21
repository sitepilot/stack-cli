<?php

namespace App\Commands;

use App\Stack;
use App\Command;

class ConfigCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config {service?} {--format=yaml} {--debug}';

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
        $name = $this->argument('service');

        if ($name && !$service = $this->service($name)) {
            return 1;
        }

        if ($service ?? null) {
            $config = $service->config(!$this->option('debug'));
        } else {
            $config = Stack::config(!$this->option('debug'));
        }

        switch ($this->option('format')) {
            case 'json':
                $this->line(json_encode($config));
                break;
            case 'yaml':
            default:
                $this->line(Stack::arrayToYaml($config));
                break;
        }
    }
}
