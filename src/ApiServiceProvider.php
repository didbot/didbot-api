<?php

namespace Didbot\DidbotApi;

use App;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/../routes.php';
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        Passport::routes();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        App::register(\Laravel\Passport\PassportServiceProvider::class);
    }
}