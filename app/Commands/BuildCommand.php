<?php

namespace App\Commands;

use App\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BuildCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'build {image=all}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Build stack container images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $images = array_filter([
            'lshttpd' => 'docker/lshttpd',
            'runtime:7.4' => 'docker/runtime/7.4',
            'runtime:8.0' => 'docker/runtime/8.0'
        ], function ($image) {
            if ($this->argument('image') == 'all' || $this->argument('image') == $image) {
                return true;
            }
        }, ARRAY_FILTER_USE_KEY);

        if (!count($images)) {
            $this->error("Unknown container image: " . $this->argument('image'));
            return 1;
        }

        $processes = [];

        foreach ($images as $image => $path) {
            File::deleteDirectory(stack_config_path($path));

            File::copyDirectory(stack_path($path), stack_config_path($path));

            foreach (File::allFiles(stack_config_path($path . DIRECTORY_SEPARATOR . 'bin')) as $file) {
                File::chmod($file->getPathname(), 0755);
            }

            $processes[$image] = (new Process(['docker', 'build', '-t', "ghcr.io/sitepilot/$image", stack_config_path($path)]))
                ->setTty(false)
                ->setTimeout(900)
                ->setTty(false);

            $processes[$image]->start();
        }

        foreach ($processes as $image => $process) {
            $this->task("Build $image ", function () use ($image, $process) {
                while ($process->isRunning()) {
                    // waiting for process to finish
                }

                return true;
            });
        }
    }
}
