<?php

namespace  App\Commands;

use App\Command;
use App\Services\Site\SiteService;

class SiteCreateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'site:create
        {name : The site name.}
        {--d|domains= : A comma separated list of domains.}
        {--t|tag= : The runtime tag.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new site';

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

        $site = new SiteService($name, [
            'tag' => $tag,
            'domains' => explode(",", $domains),
        ]);

        $site->init();

        $this->info("----------------");
        $this->info("Name: $name");
        $this->info("Tag: $tag");
        $this->info("Domains: $domains");
        $this->info("----------------\n");
    }
}
