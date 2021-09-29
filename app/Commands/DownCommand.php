<?php

namespace App\Commands;

use App\Command;

class DownCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'down {--destroy : Destroy service data.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Stop service containers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $destroy = $this->option('destroy');

        if ($destroy) {
            $destroy = $this->confirm('Are you sure you want to destroy all service data?');
        }

        $this->task("Stop stack services", function () use ($destroy) {
            $this->compose->down($destroy);
        });
    }
}
