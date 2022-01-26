<?php

namespace HeadlessLaravel;

use Composer\Autoload\ClassMapGenerator;
use HeadlessLaravel\Cards\Card;
use HeadlessLaravel\Cards\CardGroup;
use HeadlessLaravel\Formations\Formation;
use Illuminate\Support\Facades\Route;

class Headless
{
    public function formations()
    {
        $formations = [];
        $formationsPath = config('headless-laravel.formations_path');

        if (!file_exists($formationsPath)) {
            return $formations;
        }

        $formations = $this->getClasses($formationsPath);

        foreach ($formations as $formationName) {
            /** @var Formation $formation */
            $formation = app($formationName);
            Route::formation($formationName)
                ->resource((string)$formation->guessResourceName()); // guessResourceName needs a PR to formation repo
        }
    }

    public function cards()
    {
        $cards = [];
        $cardsPath = config('headless-laravel.cards_path');

        if (!file_exists($cardsPath)) {
            return $cards;
        }

        $cards = $this->getClasses($cardsPath);

        foreach ($cards as $cardName) {
            /** @var CardGroup $cardGroup */
            $cardGroup = app($cardName);
            /** @var Card $card */
            Route::cards($cardGroup->guessEndpointName(), $cardName); // // guessEndpointName needs a PR to cards repo
        }
    }

    private function getClasses($dirPath)
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
    }
}
