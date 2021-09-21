<?php

namespace App\Providers;

use App\Stack;
use Exception;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Stack::uid() === 0) {
            throw new Exception("It looks like you're running the command as root, to avoid security issues and permission errors this is not allowed.");
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Stack::loadEnv();
    }
}
