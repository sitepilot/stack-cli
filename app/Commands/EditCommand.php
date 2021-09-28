<?php

namespace App\Commands;

use Exception;
use App\Command;
use Symfony\Component\Process\Process;

class EditCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'edit
        {--e|environment : Edit stack environment}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Edit stack configuration';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('environment')) {
            $file = $this->config->envFile();
        } else {
            $file = $this->config->stackFile();
        }

        try {
            (new Process(['vi', $file]))
                ->setTty(Process::isTtySupported())
                ->setTimeout(0)
                ->mustRun();
        } catch (Exception $e) {
            abort(1, $e->getMessage());
        }

        $this->task("Validate stack configuration", function () {
            $this->services->validate();
        });
    }
}
