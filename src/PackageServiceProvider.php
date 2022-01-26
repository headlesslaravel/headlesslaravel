<?php

namespace HeadlessLaravel;

use HeadlessLaravel\Commands\InstallCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }

        $this->app->singleton(Headless::class, function () {
            return new Headless();
        });

        $this->mergeConfigFrom(__DIR__.'/../config/headless-laravel.php', 'headless-laravel');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js'),
        ], 'craniums-vue');

        Route::macro('headless', function () {
            return app(Headless::class)->route();
        });
    }
}
