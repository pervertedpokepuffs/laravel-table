<?php

namespace Sysniq\LaravelTable;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Sysniq\LaravelTable\Console\CreateTable;

class LaravelTableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-table.php', 'laravel-table');
        $this->registerCommands();

        // $this->publishConfig();
        $this->publishAssets();
        $this->publishViews();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'st');

        Blade::directive('laravelTableAssets', function () {
            return <<< HTML
                <script src="{{ asset('vendor/sysniq/app-laravel-table.js') }}" defer></script>
                <link href="{{ asset('vendor/sysniq/laravel-table.css') }}" rel="stylesheet" />
            HTML;
        });
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        });
    }

    /**
     * Get route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'namespace'  => "Sysniq\LaravelTable\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register facade
        $this->app->singleton('laravel-table', function () {
            return new LaravelTable;
        });
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole())
            $this->commands([
                CreateTable::class,
            ]);
    }

    /**
     * Publish Config
     *
     * @return void
     */
    public function publishConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-table.php' => config_path('laravel-table.php'),
            ], 'laravel-table.config');
        }
    }

    /**
     * Publish views
     * 
     * @return void
     */
    public function publishViews()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views/table-actions' => resource_path('views/vendor/sysniq/views'),
            ], 'laravel-table.views');
        }
    }

    /**
     * Publish assets
     * 
     * @return void
     */
    public function publishAssets()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/css' => resource_path('vendor/sysniq/css'),
                __DIR__ . '/../resources/js' => resource_path('vendor/sysniq/js'),
                __DIR__ . '/../resources/assets' => public_path('vendor/sysniq'),
            ], 'laravel-table.assets');
        }

        $this->publishes([
            __DIR__ . '/../resources/images' => resource_path('images'),
        ], 'laravel-table.assets');
    }
}
