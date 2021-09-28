<?php

namespace App\Commands;

use Exception;
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
    protected $signature = 'build {image=all} {--no-cache}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Build service container images';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $images = array_filter([
            'backup' => 'docker/backup',
            'lshttpd' => 'docker/lshttpd',
            'runtime:7.4' => 'docker/runtime/7.4',
            'runtime:8.0' => 'docker/runtime/8.0'
        ], function ($image) {
            if ($this->argument('image') == 'all' || $this->argument('image') == $image) {
                return true;
            }
        }, ARRAY_FILTER_USE_KEY);

        if (!count($images)) {
            abort(1, "Unknown container image: " . $this->argument('image'));
        }

        foreach ($images as $image => $path) {
            $this->task("Build $image image", function () use ($image, $path) {
                File::deleteDirectory($this->config->path($path));

                File::copyDirectory(base_path($path), $this->config->path($path));

                foreach (File::allFiles($this->config->path($path . DIRECTORY_SEPARATOR . 'bin')) as $file) {
                    File::chmod($file->getPathname(), 0755);
                }

                $this->line("");

                $imageURL = "ghcr.io/sitepilot/$image";
                $command = ['docker', 'build'];

                try {
                    if (!$this->option('no-cache')) {
                        (new Process(['docker', 'pull', $imageURL]))
                            ->setTimeout(900)
                            ->setTty(Process::isTtySupported())
                            ->run();

                        $command = array_merge($command, ['--cache-from', $imageURL]);
                    }

                    (new Process(array_merge($command, ['-t', $imageURL, $this->config->path($path)])))
                        ->setTimeout(900)
                        ->setTty(Process::isTtySupported())
                        ->mustRun();
                } catch (Exception $e) {
                    abort(1, $e->getMessage());
                }
            });
        }
    }
}
