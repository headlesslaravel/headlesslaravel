<?php

namespace HeadlessLaravel;

use HeadlessLaravel\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        if (class_exists(Inertia::class)) {
            Inertia::share('dateIntervals', DateInterval::make());
        }

        $this->publishes([
            __DIR__.'/../stubs/general'               => base_path(),
            __DIR__.'/../config/headless-laravel.php' => config_path('headless-laravel.php'),
        ], 'headless-setup');

        $this->publishes([
            __DIR__.'/../stubs/vue/' => base_path(),
        ], 'craniums-vue');
    }
}
