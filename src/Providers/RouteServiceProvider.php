<?php

namespace TaskManagement\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for the application.
     * @var string|null
     */
    protected $namespace = 'TaskManagement\\Http\\Controllers';

    /**
     * @uses Define your route model bindings, pattern filters, etc.
     * @return void
     */
    final public function boot()
    {
        parent::boot();
    }

    /**
     * @uses Define the routes for the application.
     * @return void
     */
    final public function map()
    {
        // this for register v1 Frontend endpoint
        $this->mapFrontendRoute("v1");
        $this->mapFrontendPipelineRoute("v1");
    }

    /**
     * Define the frontend folder routes files
     * @param String $version
     * @return void
     */
    final private function mapFrontendRoute(string $version = 'v1'): void
    {
        Route::prefix("api/$version/service/task")
            ->namespace($this->namespace)
            ->group(__DIR__ . "/../Routes/Frontend/{$version}/api.php");
    }

    /**
     * Define the frontend folder routes files
     * @param String $version
     * @return void
     */
    final private function mapFrontendPipelineRoute(string $version = 'v1'): void
    {
        Route::prefix("api/$version/service/pipeline-task")
            ->namespace($this->namespace . "\\Pipeline")
            ->group(__DIR__ . "/../Routes/Frontend/{$version}/pipeline.php");
    }
}
