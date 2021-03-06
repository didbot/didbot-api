<?php
namespace Didbot\DidbotApi\Test;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\BrowserKit\TestCase as Orchestra;
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

        // Migrate laravel/passport tables
        $this->artisan('migrate', ['--path' => '/../vendor/laravel/passport/database/migrations']);

        // Migrate package tables
        $this->artisan('migrate', ['--path' => '/../migrations/']);

        $this->artisan('migrate');
        $this->artisan('passport:install');

        $this->serverVariables = [
            'HTTP_X_REQUESTED_WITH'=> 'XMLHttpRequest'
        ];
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
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
        ]);

        $app['config']->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'database' => 'didbot_test',
            'username' => 'postgres',
            'host' => 'localhost',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
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
                'cipher'=> 'AES-256-CBC',
                'log'   => 'single'
        ]);

        $base_path = $app['path.base'];
        $db_path = $app['path.database'];
    }
}