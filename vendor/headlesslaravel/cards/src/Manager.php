<?php

namespace HeadlessLaravel\Cards;

use HeadlessLaravel\Cards\Http\Controllers\CardsController;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class Manager
{
    protected $resolved = [];

    protected $resolving;

    protected $endpoints = [];

    protected $groups = [];

    public function register(Router $router, $endpoint, $class)
    {
        $endpoint = trim($endpoint, '/');

        $key = $endpoint;

        if ($prefix = $router->getLastGroupPrefix()) {
            $key = "$prefix/$endpoint";
        }

        $this->endpoints[$key] = $class;

        $router->addRoute(['GET', 'HEAD'], $endpoint, [CardsController::class, 'index']);
        $router->addRoute(['GET', 'HEAD'], "$endpoint/{cardKey}", [CardsController::class, 'show']);
    }

    public function byObject($object)
    {
        return Arr::get($this->resolved, get_class($object), false);
    }

    public function start($object)
    {
        $this->resolving = get_class($object);
    }

    public function appendResolving(Card $card)
    {
        $this->resolved[$this->resolving][] = $card;
    }

    public function finish(): array
    {
        $resolved = $this->resolved[$this->resolving];

        // make the card values easily referenced by $card['key']
        // may or may not be useful
        //        foreach($resolved as $card) {
        //            $this->groups[$this->resolving][$card['key']] = $card;
        //        }

        $this->resolving = null;

        return $resolved;
    }

    public function current(): CardGroup
    {
        $endpoint = $this->baseEndpoint();

        return app(Arr::get($this->endpoints, $endpoint));
    }

    public function currentByKey($key): Card
    {
        $endpoint = $this->baseEndpoint();

        $group = Arr::get($this->endpoints, $endpoint);

        return $this->groups[$group][$key]; // maybe should be card object vs array
    }

    public function baseEndpoint(): string
    {
        $route = Route::current();

        $uri = $route->uri();

        if ($route->hasParameter('cardKey')) {
            $uri = trim(str_replace('{cardKey}', '', $uri), '/');
        }

        return $uri;
    }
}
