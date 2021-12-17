<?php

namespace HeadlessLaravel\Formations;

use HeadlessLaravel\Formations\Exceptions\UnregisteredFormation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;

class Manager
{
    /**
     * The current resource.
     *
     * @var
     */
    protected $current;

    /**
     * The resources.
     *
     * @var array
     */
    protected $resources = [];

    /**
     * Retrieve all resources.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->resources;
    }

    /**
     * Register a new resource.
     *
     * @param array $resource
     */
    public function register(array $resource)
    {
        if($resource['parent'] && is_null($this->resource($resource['parent']))) {
            throw new UnregisteredFormation("Unknown parent formation: {$resource['parent']}");
        }

        $this->resources[] = $resource;
    }

    /**
     * Find a resource by key
     *
     * @param $key
     * @return ?array
     */
    public function resource($key): ?array
    {
        foreach ($this->resources as $resource) {
            if($resource['resource'] === $key) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * The current formation object.
     *
     * @return Formation|null
     */
    public function formation($key = null): ?Formation
    {
        if(is_null($key)) {
            return app(Arr::get($this->current(), 'formation'));
        }

        foreach ($this->resources as $resource) {
            if($resource['resource'] === $key) {
                return app($resource['formation']);
            }
        }

        return null;
    }

    /**
     * The current resource settings.
     *
     * @return mixed|null
     */
    public function current()
    {
        if($this->current) {
            return $this->current;
        }

        $name = Request::route()->getName();

        foreach ($this->resources as $resource) {
            foreach ($resource['routes'] as $route) {
                if ($route['key'] === $name) {
                    $this->current = $resource;
                    return $resource;
                }
            }
        }

        abort(500, "No resource with route name: $name");
    }

    public function hasParent()
    {
        return Arr::get($this->current(), 'parent') != null;
    }
}
