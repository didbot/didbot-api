<?php

namespace Didbot\DidbotApi;

use App;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/../routes.php';
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $router->middleware('ReturnJson', '\Didbot\DidbotApi\Middleware\ReturnJson');

        Passport::routes();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        App::register(\Laravel\Passport\PassportServiceProvider::class);
        App::register(\Spatie\Fractal\FractalServiceProvider::class);
    }
}