<?php

namespace HeadlessLaravel;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Support\Facades\Route;

class Manager
{
    public $searchable = [];

    public $formations = [];

    public $cards = [];

    public function searchables(): array
    {
        if (count($this->searchable)) {
            return $this->searchable;
        }

        $formationsPath = config('headless-laravel.search');

        if (is_array($formationsPath)) {
            return $formationsPath;
        }

        if (!file_exists($formationsPath)) {
            return [];
        }

        $classes = $this->getClasses($formationsPath);

        foreach ($classes as $class) {
            if (app($class)->globalSearch !== false) {
                $this->searchable[] = $class;
            }
        }

        return $this->searchable;
    }

    public function formations(): array
    {
        if (count($this->formations)) {
            return $this->formations;
        }

        $formationsPath = config('headless-laravel.paths.formations');

        if (!file_exists($formationsPath)) {
            return [];
        }

        $this->formations = $this->getClasses($formationsPath);

        return $this->formations;
    }

    public function cards(): array
    {
        if (count($this->cards)) {
            return $this->cards;
        }

        $cardsPath = config('headless-laravel.paths.cards');

        if (!file_exists($cardsPath)) {
            return [];
        }

        $this->cards = $this->getClasses($cardsPath);

        return $this->cards;
    }

    public function formationRoutes()
    {
        foreach ($this->formations() as $class) {

            $formation = app($class);

            Route::formation($class)
                ->resource((string) $formation->guessResourceName());

            if(count($formation->import())) {
                Route::formation($class)
                    ->resource((string) $formation->guessResourceName())
                    ->asImport();
            }
        }
    }

    public function cardRoutes()
    {
        foreach ($this->cards() as $class) {
            Route::cards($class);
        }
    }

    public function notificationRoutes()
    {
        Route::notifications();
    }

    public function searchRoutes()
    {
        $searchable = $this->searchables();

        if (count($searchable)) {
            Route::seeker($searchable);
        }
    }

    private function getClasses($dirPath): array
    {
        $classMap = ClassMapGenerator::createMap($dirPath);

        // Sort list so it's stable across different environments
        ksort($classMap);

        $classes = [];

        foreach ($classMap as $className => $path) {
            $classes[] = $className;
        }

        return $classes;
    }

    public function routes()
    {
        $this->formationRoutes();
        $this->searchRoutes();
        $this->cardRoutes();
        $this->notificationRoutes();
    }
}
