<?php

namespace HeadlessLaravel\Formations\Http\Controllers;

class NestedController extends Controller
{
    public function index()
    {
        $this->check('viewAny', $this->model());

        $results = $this->formation()->nest(
            $this->parentFormation(),
            $this->parent()->getKey()
        )->results();

        return $this->response('index', $results);
    }

    public function create()
    {
        $this->check('create', $this->model());

        return $this->response('create');
    }

    public function store()
    {
        $this->check('create', $this->model());

        $resource = $this->model()->create($this->values());

        return $this->response('store', $resource);
    }

    public function show()
    {
        $resource = $this->resource();

        $this->check('view', $resource);

        return $this->response('show', $resource);
    }

    public function edit()
    {
        $resource = $this->resource();

        $this->check('update', $resource);

        return $this->response('edit', $resource);
    }

    public function update()
    {
        $resource = $this->resource();

        $this->check('update', $resource);

        $resource->update($this->values());

        return $this->response('update', $resource);
    }

    public function destroy()
    {
        $resource = $this->resource();

        $this->check('delete', $resource);

        $resource->delete();

        return $this->response('destroy', $resource);
    }

    public function restore()
    {
        $resource = $this->resource();

        $this->check('restore', $resource);

        $resource->restore();

        return $this->response('restore', $resource);
    }

    public function forceDelete()
    {
        $resource = $this->resource();

        $this->check('forceDelete', $resource);

        $resource->forceDelete();

        return $this->response('force-delete', $resource);
    }
}
