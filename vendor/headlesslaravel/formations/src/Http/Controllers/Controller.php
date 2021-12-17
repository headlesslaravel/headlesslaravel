<?php

namespace HeadlessLaravel\Formations\Http\Controllers;

use App\Http\Resources\AddressResource;
use HeadlessLaravel\Formations\Http\Resources\Resource;
use HeadlessLaravel\Formations\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller extends BaseController
{
    public $current;

    public $terms = [];

    protected $resolvedParent;

    protected $resolvedResource;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(Manager $manager)
    {
        $this->middleware(function ($request, $next) use ($manager) {
            $this->current = $manager->current();

            $this->resolveParentBinding();
            $this->resolveResourceBinding();

            return $next($request);
        });
    }

    protected function resolveResourceBinding()
    {
        if ($this->shouldResolveResource()) {
            $this->resolvedResource = $this->resource();
            Route::current()->setParameter(
                $this->current['resource_route_key'],
                $this->resolvedResource
            );
        }
    }

    public function shouldResolveResource():bool
    {
        $method = $this->controllerMethod();

        return ! in_array($method, ['index', 'create', 'store', 'sync', 'attach', 'detach']);
    }

    protected function resolveParentBinding()
    {
        // TODO: determine IF should resolve // if exists etc

        $this->resolvedParent = $this->parent();

        if(! $this->resolvedParent) {
            return;
        }

        Route::current()->setParameter(
            $this->current['parent_route_key'],
            $this->resolvedParent
        );
    }

    public function values()
    {
        $method = $this->controllerMethod();

        if($this->resolvedParent) {
            Request::merge([
                $this->parentForeignKey()
                => $this->resolvedParent->getKey()
            ]);
        }

        if($method === 'store') {
            $request = $this->createRequest();
        } else {
            $request = $this->updateRequest();
        }

        if (! empty($request->rules())) {
            $request->validateResolved();
            return $request->validated();
        }
//        if($method === 'store' && count($this->createRequestRules())) {
//            return $this->createRequest()->validated();
//        } else if($method === 'update' && count($this->updateRequestRules())) {
//            return $this->updateRequest()->validated();
//        }

        return Request::all();
    }

    public function check($ability, $arguments = [])
    {
        if ($this->hasPolicyMethod($ability)) {
            $this->authorize($ability, $arguments);
        }
    }

    public function hasPolicyMethod($method): bool
    {
        $policy = Gate::getPolicyFor($this->formation()->model);

        return $policy != false && method_exists($policy, $method);
    }

    public function formation()
    {
        return app($this->current['formation']);
    }

    public function formationWithPivot()
    {
        return $this->formation()->whereRelation(
            $this->parentRelationName(),
            $this->parentModel()->getQualifiedKeyName(),
            '=',
            $this->parentValue()
        );
    }

    public function parentFormation()
    {
        $segments = Request::segments();

        if($prefix = Route::getCurrentRoute()->getPrefix()) {
            if($segments[0] == $prefix) {
                unset($segments[0]);
                $segments = array_values($segments);
            }
        }

        return app(Manager::class)->formation($segments[0]);
    }

    public function parentRelation()
    {
        $relation = $this->resourceRelationName();

        return $this->parent()->$relation();
    }

    public function resourceRelationName()
    {
        return $this->terms('resource.camelPlural');
    }

    public function parentRelationName()
    {
        return $this->terms('parent.camelPlural');
    }

    public function model()
    {
        return app($this->formation()->model);
    }

    public function parentModel()
    {
        return app($this->parentFormation()->model);
    }

    public function createRequest(): FormRequest
    {
        return app($this->formation()->create);
    }

    public function updateRequest(): FormRequest
    {
        return app($this->formation()->update);
    }

    public function resource()
    {
        if ($this->resolvedResource) {
            return $this->resolvedResource;
        }

        if(is_a($this, PivotController::class)) {
            return $this->pivotResource();
        }

        $query = $this->model()->where(
            $this->model()->getKeyName(),
            $this->resourceValue()
        );

        if($this->resolvedParent) {
            $query->where([
                $this->parentForeignKey()
                => $this->resolvedParent->getKey(),
            ]);
        }

        $query = $this->withTrashed($query);

        $method = $this->controllerMethod();

        $this->formation()->queryCallback($method, $query);

        return $query->firstOrFail();
    }

    public function pivotResource()
    {
        $query = $this->parentRelation();

        $query = $this->withTrashed($query);

        // TODO: determine queryCallback
        return $query->firstOrFail();
    }

    public function parent()
    {
        if(! app(Manager::class)->hasParent()) {
            return null;
        }

        if ($this->resolvedParent) {
            return $this->resolvedParent;
        }

        $query = $this->parentModel();
        $query = $this->withTrashed($query);

        $query = $query->where(
            $this->parentModel()->getKeyName(),
            $this->parentValue()
        );

        // TODO: determine if it should be showQuery
        // or if it should be parentUpdateQuery()
        // or should be both?
//        $method = Route::current()->getActionMethod();
//        $this->parentFormation()->queryCallback($method, $query);

        return $query->firstOrFail();
    }

    private function withTrashed($query)
    {
        $subject = is_a($query, Model::class)
            ? $query
            : $query->getModel();

        if (Request::route()->allowsTrashedBindings()
            && method_exists($subject, 'bootSoftDeletes')) {
            return $query->withTrashed();
        }

        return $query;
    }
    public function route($key)
    {
        $route = collect($this->current['routes'])->firstWhere('type', $key);

        return $route['key'];
    }

    public function transform($attributes, $extra = [])
    {
        return $this->applyResource(
            $this->formation()->resource,
            $attributes,
            $extra,
        );
    }

    public function transformParent($attributes)
    {
        return $this->applyResource(
            $this->parentFormation()->resource,
            $attributes,
        );
    }

    protected function applyResource($resource, $attributes, $extra = [])
    {
        if($this->mode() === 'api' && $this->controllerMethod() == 'index') {
            $resource::wrap($this->terms('resource.slugPlural'));
        } else if($this->mode() === 'api') {
            $resource::wrap($this->terms('resource.slug'));
        }
//        else if($this->mode() === 'inertia' && $this->controllerMethod() == 'index') {
//            $class::wrap($this->terms('resource.camelPlural'));
//        } else if($this->mode() === 'inertia') {
//            $class::wrap($this->terms('resource.camel'));
//        }

        if (is_a($attributes, LengthAwarePaginator::class, true)) {
            return $resource::collection($attributes)->additional($extra);
        }

        return $resource::make($attributes)->additional($extra);
    }

    public function response(string $type, $props = null)
    {
        if($this->shouldFlash($type)) {
            $this->flash($type, $props);
        }

        if ($this->shouldRedirect($type)) {
            return $this->redirect($type, $props);
        }

        return match ($this->mode()) {
            'api' => $this->api($type, $props),
            'inertia' => $this->inertia($type, $props),
            'blade' => $this->blade($type, $props)
        };
    }

    public function api($type, $props = null)
    {
        $data = $this->formation()->dataCallback($type, [], $props);

        if($data === $props) {
            $data = [];
        }

        if($this->resolvedParent) {
            $extra = array_merge([
                $this->parentKey() => $this->transformParent($this->resolvedParent)
            ], $data);

            if(is_a($props, AbstractPaginator::class)) {
                return JsonResource::collection($props)->additional($extra);
            } else if(is_a($props, Model::class)) {
                return JsonResource::make($props)->additional($extra);
            } else if(is_array($props)) {
                return array_merge($props, $extra);
            } else {
                return $extra;
            }
        }

        return $this->transform($props, $data);
    }

    public function inertia($type, $props = null)
    {
        $term = null;

        if ($type === 'index') {
            $term = 'resource.camelPlural';
        } elseif (in_array($type, ['show', 'edit'])) {
            $term = 'resource.camel';
        }

        $data = [];

        if ($term) {
            $data = [ $this->terms($term) => $this->transform($props) ];
        }

        $data = $this->formation()->dataCallback($type, $data, $props);

        $view = $this->terms('resource.studlyPlural').'/'.ucfirst($type);

        return Inertia::render($view, $data);
    }

    public function blade($type, $props = null): mixed
    {
        $view = $this->terms('resource.slugPlural').'.'.$type;

        if(app()->environment('testing')) {
            $view = "testing::$view";
        }

        $data = [];

        if($type === 'index') {
            $data = [ $this->terms('resource.slugPlural') => $props];
        } else if (in_array($type, ['show', 'edit'])) {
            $data = [ $this->terms('resource.slug') => $props];
        }

        $data = $this->formation()->dataCallback($type, $data, $props);

        if(is_null($data)) {
            return View::make($view);
        }

        return View::make($view)->with($data);
    }

    public function redirect($type, $props)
    {
        if (in_array($type, ['store', 'update', 'restore'])) {
            if($this->resolvedParent) {
                $props = [$this->resolvedParent, $props];
            }
            $redirect = [
                'url' => route($this->route('show'), $props),
            ];
        } else {
            $redirect = [
                'url' => route($this->route('index')),
            ];
        }

        if(Request::hasHeader('Show-Redirect-Url')) {
            return response()->json($redirect);
        }

        return redirect()->to($redirect['url']);
    }

    public function shouldRedirect($type): bool
    {
        if ($this->mode() === 'api') {
            return false;
        }

        return in_array($type, [
            'store', 'update', 'restore',
            'destroy', 'force-delete',
        ]);
    }

    public function shouldFlash($type): bool
    {
        if ($this->mode() === 'api') {
            return false;
        }

        return in_array($type, [
            'store', 'update', 'restore',
            'destroy', 'force-delete',
        ]);
    }

    public function terms($key = null)
    {
        if (empty($this->terms)) {
            $this->terms['resource'] = $this->getTerms($this->current['resource']);
            if(isset($this->current['parent'])) {
                $this->terms['parent'] = $this->getTerms($this->current['parent']);
            }
        }

        return Arr::get($this->terms, $key);
    }

    public function flash($type, $resource)
    {
        $display = $this->formation()->display;
        $display = Arr::get($resource, $display, 'a resource');
        $message = trans('formations::flash.'. $type, ['resource' => $display]);

        Session::flash('flash', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    protected function getTerms($resource)
    {
        $resource = Str::of($resource)->replace('-', ' ')->lower();

        return [
            'lower' => (string) $resource,
            'lowerPlural' => (string) Str::of($resource)->plural(),
            'studly' => (string) Str::of($resource)->singular()->studly(),
            'studlyPlural' => (string) Str::of($resource)->plural()->studly(),
            'snake' => (string) Str::of($resource)->singular()->snake(),
            'snakePlural' => (string) Str::of($resource)->snake()->plural(),
            'slug' => (string) Str::of($resource)->singular()->slug(),
            'slugPlural' => (string) Str::of($resource)->slug()->plural(),
            'camel' => (string) Str::of($resource)->singular()->camel(),
            'camelPlural' => (string) Str::of($resource)->camel()->plural(),
        ];
    }

    protected function parentKey()
    {
        return Arr::get($this->current, 'parent_route_key');
    }

    protected function resourceKey()
    {
        return Arr::get($this->current, 'resource_route_key');
    }

    protected function parentValue()
    {
        return Route::current()->originalParameter($this->parentKey());
    }

    protected function resourceValue()
    {
        return Route::current()->originalParameter($this->resourceKey());
    }

    protected function mode(): string
    {
        if(Request::hasHeader('Wants-Json')) {
            return 'api';
        }

        return config('formations.mode', 'blade');
    }

    protected function parentForeignKey(): string
    {
        return $this->parentFormation()->getForeignKey();
    }

    protected function controllerMethod(): string
    {
        return Route::current()->getActionMethod();
    }
}
