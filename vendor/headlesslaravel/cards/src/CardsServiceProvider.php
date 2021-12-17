<?php

namespace HeadlessLaravel\Cards;

use HeadlessLaravel\Cards\Commands\CardsMakeCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CardsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cards.php',
            'cards'
        );

        $this->app->singleton(Manager::class, function ($app) {
            return new Manager();
        });
    }

    public function boot()
    {
        Route::macro('cards', function ($endpoint, $handler) {
            return app(Manager::class)->register($this, $endpoint, $handler);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                CardsMakeCommand::class,
            ]);
        }
    }
}
