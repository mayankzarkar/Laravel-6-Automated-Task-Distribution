<?php

namespace TaskManagement\Providers;

use Illuminate\Support\ServiceProvider;
use TaskManagement\Console\Commands\FixTaskTypeData;
use TaskManagement\Console\Commands\UserTaskReminderCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @uses function register for Register services.
     * @return void
     */
    public function register(): void
    {
        // this for register new database when use workflow service
        $this->registerDatabase();

        // this for check this exec it's CMD or http request
        if ($this->app->runningInConsole()) {

            // command running on CMD
            $this->commands([
                UserTaskReminderCommand::class,
                FixTaskTypeData::class
            ]);
        }

        // register new config file
        $this->registerConfig();

        $is_local = app()->environment('local');

        if (config('task_management.active_middleware') || $is_local) {
            $this->registerMiddleware();
        }

        if (config('task_management.active_routes') || $is_local) {
            $this->app->register(RouteServiceProvider::class);
        }

        if (config('task_management.active_event') || $is_local) {
            $this->app->register(EventServiceProvider::class);
        }
    }

    /**
     * @uses  function boot for Bootstrap services.
     * @return void
     */
    public function boot(): void
    {
        // Load translation en ar on laravel project
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/Lang', 'task_management');
    }

    /**
     * @note Register extra middleware file's for example connect with account
     *       and company service to use on other service.
     */
    public function registerMiddleware()
    {
        // $this->app['router']->aliasMiddleware('middleware' , ExampleMiddleware::class);
    }

    /**
     * @uses Register config file for Helper services.
     * @return void
     */
    protected function registerConfig(): void
    {
        // merge config file as index workflow
        $this->mergeConfigFrom(__DIR__ . '/../Config/task_management.php', 'task_management');
    }

    /**
     * @note Register new connection database for service.
     */
    final protected function registerDatabase()
    {
        // get connection read and write to MongoDB
        $connection = [
            'driver'   => 'mongodb',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', 27017),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $database = config()->get('database.connections');
        $database['service.task_management.read'] = array_merge($connection, [
            'database' => env('SERVICES_TASK_MANAGEMENT_DATABASE', 'task_management')
        ]);

        $database['service.task_management.write'] = array_merge($connection, [
            'database' => env('SERVICES_TASK_MANAGEMENT_DATABASE', 'task_management')
        ]);

        config()->set('database.connections', $database);
    }
}
