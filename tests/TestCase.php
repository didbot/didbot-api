<?php
namespace Didbot\DidbotApi\Test;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factory;
use Laravel\Passport\Console\InstallCommand;

abstract class TestCase extends Orchestra
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        // Path to Model Factories (within your package
        $this->withFactories(__DIR__ . '/factories');


        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

        // Migrate laravel/passport tables
        $artisan->call('migrate', [
                '--realpath' => realpath(__dir__ . '/../vendor/laravel/passport/database/migrations')
        ]);

        // Migrate package tables
        $artisan->call('migrate', [
                '--realpath' => realpath(__dir__ . '/../migrations/')
        ]);

        // Migrate test only tables
        $artisan->call('migrate', [
                '--realpath' => __dir__ . '/migrations'
        ]);

        $this->artisan('passport:install');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Didbot\DidbotApi\ApiServiceProvider'
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
        ]);

        $app['config']->set('auth.guards.api', [
            'driver' => 'passport',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => \Didbot\DidbotApi\Test\Models\User::class,
        ]);

        $app['config']->set('app', [
                'debug' => true,
                'key'   => str_random(32),
                'cipher'=> 'AES-256-CBC'
        ]);

        $base_path = $app['path.base'];
        $db_path = $app['path.database'];
    }
}