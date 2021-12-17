<?php

namespace HeadlessLaravel\Formations\Http\Controllers;

class PivotController extends Controller
{
    public function index()
    {
        $formation = $this->formationWithPivot();

        return $this->response('index', $formation->results());
    }

    public function show()
    {
        return $this->response('show', $this->resource());
    }

    public function sync()
    {
        return $this->parentRelation()->sync(request()->input('selected'));
    }

    public function attach()
    {
        $attach = request()->input('selected');

        $this->parentRelation()->attach($attach);

        return [
            'attached' => $attach
        ];
    }

    public function detach()
    {
        $detach = request()->input('selected');

        $this->parentRelation()->detach($detach);

        return [
            'detached' => $detach
        ];
    }

    public function toggle()
    {
        $toggle = request()->input('selected');

        return $this->parentRelation()->toggle($toggle);
    }
}
