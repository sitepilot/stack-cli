<?php

namespace App\Commands;

use App\Command;

class UpCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'up';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start service containers';

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

        $this->task("Initialize configuration", function () {
            $this->init();
        });

        $this->task("Start stack containers", function () {
            $this->compose(['up', '-d', '--remove-orphans'])->mustRun();
        });
    }
}
