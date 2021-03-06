<?php

namespace Didbot\DidbotApi;

use App;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        $router->aliasMiddleware('didbot.xml-http-request', \Didbot\DidbotApi\Middleware\XmlHttpRequest::class);
        $router->aliasMiddleware('didbot.throttle', \Didbot\DidbotApi\Middleware\ThrottleRequest::class);

        Passport::routes();


        Validator::extend('iso8601', function ($attribute, $value, $parameters, $validator) {

            return (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/',
                    $value) === 1);
        });

        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {

            return (preg_match('/^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$/i',
                    $value) === 1);

        });

        Validator::extend('geo', function ($attribute, $value, $parameters, $validator) {

            return (preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/',
                    $value) === 1);

        });


        // Custom names for the Polymorphic relation on the Didbot\DidbotApi\Models\Source model
        Relation::morphMap([
            'client' => 'Laravel\Passport\Client',
            'token' => 'Laravel\Passport\Token',
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        App::register(\Laravel\Passport\PassportServiceProvider::class);
        App::register(\Spatie\Fractal\FractalServiceProvider::class);
        App::register(\Phaza\LaravelPostgis\DatabaseServiceProvider::class);
    }
}