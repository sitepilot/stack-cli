<?php

namespace App\Commands;

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
        $this->init();

        if ($this->option('environment')) {
            $file = stack_project_path('.env');
        } else {
            $file = stack_project_path('stack.yml');
        }

        (new Process(['vi', $file]))
            ->setTimeout(0)->setTty(Process::isTtySupported())->mustRun();

        if ($this->validate()) {
            $this->task("Updating stack configuration", function () {
                $this->init();
            });

            return 0;
        }

        return 1;
    }
}
