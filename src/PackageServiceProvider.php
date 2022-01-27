<?php

namespace HeadlessLaravel;

use HeadlessLaravel\Commands\InstallCommand;
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

        $this->app->singleton(Manager::class, function () {
            return new Manager();
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
            __DIR__.'/../stubs/general' => app_path(),
        ], 'headless-setup');

        $this->publishes([
            __DIR__.'/../stubs/vue/' => app_path(),
        ], 'craniums-vue');
    }
}
