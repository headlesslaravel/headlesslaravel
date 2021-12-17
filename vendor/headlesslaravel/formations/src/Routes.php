<?php

namespace HeadlessLaravel\Formations;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Routes
{
    public $parent;

    public $resource;

    public $pivot = false;

    protected $types = [];

    protected $formation;

    protected $prefix;

    protected $manager;

    protected $router;

    public function __construct(Manager $manager, Router $router)
    {
        $this->manager = $manager;

        $this->router = $router;

        $this->prefix = $this->router->getLastGroupPrefix();
    }

    public function setResource($resource)
    {
        $this->parent = Str::before($resource, '.');
        $this->resource = Str::after($resource, '.');

        if($this->parent === $this->resource) {
            $this->parent = null;
        }

        return $this;
    }

    public function setFormation($formation)
    {
        $this->formation = $formation;

        return $this;
    }

    public function setTypes(array $types = [])
    {
        $this->types = $types;

        return $this;
    }

    public function setPrefix($prefix = null):self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function make()
    {
        return array_map(function($endpoint) {
            return array_merge($endpoint, [
                'with-trashed' => $this->withTrashed($endpoint['type']),
                'action' => $this->makeAction($endpoint['type']),
                'name' => $this->makeName($endpoint['type']),
                'key' => $this->makeKey($endpoint['type']),
            ]);
        }, $this->endpoints());
    }

    public function withTrashed($name):bool
    {
        return true; // todo: determine parent and child trash state
        return in_array($name, ['show', 'restore', 'force-delete']);
    }

    public function makeName($name)
    {
        if($this->parent) {
            return "$this->parent.$this->resource.$name";
        }

        return "$this->resource.$name";
    }

    public function makeKey($name):string
    {
        $output = "$this->resource.$name";

        if($this->parent) {
            $output = "$this->parent.$output";
        }


        if($this->prefix) {
            $output = "$this->prefix.$output";
        }

        return $output;
    }

    public function makeAction($name): array
    {
        $name = Str::camel($name);

        if($this->pivot) {
            return [app($this->formation)->pivotController, $name];
        } else if($this->parent) {
            return [app($this->formation)->nestedController, $name];
        } else {
            return [app($this->formation)->controller, $name];
        }
    }

    public function create():self
    {
        $routes = $this->make();

        foreach ($routes as $route) {
            $this->router
                ->addRoute($route['verb'], $route['endpoint'], $route['action'])
                ->name($route['name'])
                ->withTrashed($route['with-trashed']);
        }

        $this->manager->register([
            'formation' => $this->formation,
            'resource' => $this->resource,
            'parent' => $this->parent,
            'resource_route_key' => $this->resourceRouteKey(),
            'parent_route_key' => $this->parentRouteKey(),
            'routes' => $routes,
        ]);

        return $this;
    }

    public function endpoints(): array
    {
        if($this->pivot) {
            $endpoints = $this->pivotEndpoints();
        } else if($this->parent) {
            $endpoints = $this->nestedEndpoints();
        } else {
            $endpoints = $this->resourceEndpoints();
        }

        if(!$this->types) {
            return $endpoints;
        }

        return array_filter($endpoints, function($endpoint) {
            return ! in_array($endpoint['type'], $this->types);
        });
    }

    private function resourceEndpoints(): array
    {
        $key = $this->resourceRouteKey();

        return [
            ['type' => 'index', 'verb' => ['GET', 'HEAD'], 'endpoint' => $this->resource],
            ['type' => 'create', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->resource/new"],
            ['type' => 'store', 'verb' => 'POST', 'endpoint' => "$this->resource/new"],
            ['type' => 'show', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->resource/{{$key}}"],
            ['type' => 'edit', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->resource/{{$key}}/edit"],
            ['type' => 'update', 'verb' => 'PUT', 'endpoint' => "$this->resource/{{$key}}/edit"],
            ['type' => 'destroy', 'verb' => 'DELETE', 'endpoint' => "$this->resource/{{$key}}"],
            ['type' => 'restore', 'verb' => 'PUT', 'endpoint' => "$this->resource/{{$key}}/restore"],
            ['type' => 'force-delete', 'verb' => 'DELETE', 'endpoint' => "$this->resource/{{$key}}/force-delete"],
        ];
    }

    public function nestedEndpoints(): array
    {
        $p = $this->parentRouteKey();
        $r = $this->resourceRouteKey();

        return [
            ['type' => 'index', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->parent/{{$p}}/$this->resource"],
            ['type' => 'create', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->parent/{{$p}}/$this->resource/new"],
            ['type' => 'store', 'verb' => 'POST', 'endpoint' => "$this->parent/{{$p}}/$this->resource/new"],
            ['type' => 'show', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}"],
            ['type' => 'edit', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}/edit"],
            ['type' => 'update', 'verb' => 'PUT', 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}/edit"],
            ['type' => 'destroy', 'verb' => 'DELETE', 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}"],
            ['type' => 'restore', 'verb' => 'PUT', 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}/restore"],
            ['type' => 'force-delete', 'verb' => 'DELETE', 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}/force-delete"],
        ];
    }

    private function pivotEndpoints(): array
    {
        $p = $this->parentRouteKey();
        $r = $this->resourceRouteKey();

        return [
            ['type' => 'index', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->parent/{{$p}}/$this->resource"],
            ['type' => 'show', 'verb' => ['GET', 'HEAD'], 'endpoint' => "$this->parent/{{$p}}/$this->resource/{{$r}}"],
            ['type' => 'sync', 'verb' => 'POST', 'endpoint' => "$this->parent/{{$p}}/$this->resource/sync"],
            ['type' => 'toggle', 'verb' => 'POST', 'endpoint' => "$this->parent/{{$p}}/$this->resource/toggle"],
            ['type' => 'attach', 'verb' => 'POST', 'endpoint' => "$this->parent/{{$p}}/$this->resource/attach"],
            ['type' => 'detach', 'verb' => 'DELETE', 'endpoint' => "$this->parent/{{$p}}/$this->resource/detach"],
        ];
    }

    public function makeRouteKey($key): string
    {
        return Str::of($key)->replace('-', '_')->singular();
    }

    public function parentRouteKey(): string
    {
        return $this->makeRouteKey($this->parent);
    }

    public function resourceRouteKey(): string
    {
        return $this->makeRouteKey($this->resource);
    }

    public function pivot(): self
    {
        $this->pivot = true;

        return $this;
    }

    public function __destruct()
    {
        return $this->create();
    }
}
