<?php

namespace HeadlessLaravel;

use Composer\Autoload\ClassMapGenerator;
use HeadlessLaravel\Cards\Card;
use HeadlessLaravel\Cards\CardGroup;
use HeadlessLaravel\Formations\Formation;
use Illuminate\Support\Facades\Route;

class Headless
{
    public $formations = [];

    public $cards = [];

    public function formations(): array
    {
        if (count($this->formations)) {
            return $this->formations;
        }

        $formationsPath = config('headless-laravel.formations_path');

        if (!file_exists($formationsPath)) {
            return [];
        }

        $this->formations = $this->getClasses($formationsPath);

        return $this->formations;
    }

    public function routeFormations()
    {
        $formations = $this->formations();

        foreach ($formations as $formationName) {
            /** @var Formation $formation */
            $formation = app($formationName);
            Route::formation($formationName)
                ->resource((string) $formation->guessResourceName());
        }
    }

    public function cards(): array
    {
        if (count($this->cards)) {
            return $this->cards;
        }

        $cardsPath = config('headless-laravel.cards_path');

        if (!file_exists($cardsPath)) {
            return [];
        }

        $this->cards = $this->getClasses($cardsPath);

        return $this->cards;
    }

    public function routeCards()
    {
        $cards = $this->cards();

        foreach ($cards as $cardName) {
            /** @var CardGroup $cardGroup */
            $cardGroup = app($cardName);
            /** @var Card $card */
            Route::cards($cardGroup->guessEndpointName(), $cardName);
        }
    }

    public function routeNotifications()
    {
        Route::notifications();
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

    public function create()
    {
        $this->formations();
        $this->cards();
        if (Route::hasMacro('notifications')) {
            $this->routeNotifications();
        }
    }
}
