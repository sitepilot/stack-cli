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
        if (!$this->validate()) {
            return 1;
        }

        $cmd = ['down', '--remove-orphans'];

        if ($this->option('destroy')) {
            if ($this->confirm('Are you sure you want to destroy all service data?')) {
                $cmd[] = '--volumes';
            } else {
                return 0;
            }
        }

        $this->task("Stop stack containers", function() use ($cmd) {
            $this->compose($cmd)->mustRun();
        });
    }
}
