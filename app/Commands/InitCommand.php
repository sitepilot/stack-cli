<?php

namespace App\Commands;

use App\Command;
use App\Stack;

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Initialize configuration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->task("Initialize stack configuration", function () {
            $this->init();
        });
    }
}
