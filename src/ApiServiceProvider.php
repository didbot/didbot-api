<?php

namespace Didbot\DidbotApi;

use App;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Validator;

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


        Validator::extend('iso8601', function ($attribute, $value, $parameters, $validator) {

            return (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/',
                    $value) === 1);
        });
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