<?php

namespace App\Providers;

use Illuminate\Support\Str;
use App\Services\Site\SiteService;
use Illuminate\Support\Facades\View;
use App\Repositories\ConfigRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(ConfigRepository $config)
    {
        if ($config->uid() === 0) {
            abort(1, "It looks like you're running the command as root, to avoid security issues and permission errors this is not allowed.");
        }

        $config->loadEnv();

        Config::set('stack.services', array_map(function ($service) {
            return resolve($service);
        }, config('stack.services')));

        Config::set('stack.sites', array_map(function ($site) {
            return new SiteService($site);
        }, array_keys($config->get('sites', []))));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
