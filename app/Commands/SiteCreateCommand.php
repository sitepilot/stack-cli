<?php

namespace App\Commands;

use App\Command;
use App\Services\SiteService;
use Illuminate\Support\Facades\Artisan;

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
        if (!$this->validate()) {
            return 1;
        }

        $name = $this->argument('name');

        $domains = $this->option('domains') ?: $this->ask("Domains (comma separated)", "$name.test");

        $tag = $this->option('tag') ?: $this->choice("Runtime tag", [
            '7.4' => 'PHP 7.4',
            '8.0' => 'PHP 8.0'
        ], '8.0');

        $site = (new SiteService((string) $name))->setConfig([
            'domains' => explode(",", $domains),
            'tag' => $tag
        ]);

        if ($site->validator()->fails()) {
            $this->error('Site validation failed:');

            array_map(function ($error) {
                $this->error($error);
            }, $site->validator()->errors()->all());

            return 1;
        }

        $site->init();

        $this->task("Reloading stack", function () {
            Artisan::call('reload');
        });

        $this->info("Site $name created!");
    }
}
