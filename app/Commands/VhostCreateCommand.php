<?php

namespace App\Commands;

use App\Command;
use App\Services\VhostService;

class VhostCreateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'vhost:create
        {name : The vhost name.}
        {--d|domains= : A comma separated list of domains.}
        {--t|tag= : The runtime tag.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new vhost';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');

        $domains = $this->option('domains') ?: $this->ask("Domains (comma separated)", "$name.test");

        $tag = $this->option('tag') ?: $this->choice("Runtime tag", [
            '7.4' => 'PHP 7.4',
            '8.0' => 'PHP 8.0'
        ], '8.0');

        $vhost = (new VhostService((string) $name))->setConfig([
            'domains' => explode(",", $domains),
            'tag' => $tag
        ]);

        if ($vhost->validator()->fails()) {
            $this->error('Vhost validation failed:');

            array_map(function ($error) {
                $this->error($error);
            }, $vhost->validator()->errors()->all());

            return 1;
        }

        $vhost->init();

        $this->info("Vhost $name created, don't forget to reload the stack!");
    }
}
