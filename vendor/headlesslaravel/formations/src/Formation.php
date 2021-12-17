<?php

namespace HeadlessLaravel\Formations;

use HeadlessLaravel\Formations\Http\Controllers\NestedController;
use HeadlessLaravel\Formations\Http\Controllers\PivotController;
use HeadlessLaravel\Formations\Http\Requests\CreateRequest;
use HeadlessLaravel\Formations\Http\Resources\Resource;
use HeadlessLaravel\Formations\Http\Requests\UpdateRequest;
use HeadlessLaravel\Formations\Exceptions\PageExceededException;
use HeadlessLaravel\Formations\Http\Controllers\ResourceController;
use HeadlessLaravel\Formations\Scopes\SearchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Formation
{
    use Concerns\HasData;
    use Concerns\HasQueries;

    /**
     * The select option display column.
     *
     * @var string
     */
    public $display = 'id';

    /**
     * The foreign key override.
     *
     * @var string
     */
    public $foreignKey;

    /**
     * Array of columns allowed to search by.
     *
     * @var array
     */
    public $search = [];

    /**
     * Array of columns allowed to order by.
     *
     * @var array
     */
    public $sort = ['created_at'];

    /**
     * The maximum number of items per page.
     *
     * @var int
     */
    public $maxPerPage = 100;

    /**
     * The default parameters.
     *
     * @var mixed
     */
    public $defaults = [];

    /**
     * The select overrides.
     *
     * @var mixed
     */
    public $select = [];

    /**
     * The model instance.
     *
     * @var Model
     */
    public $model;

    /**
     * The resource controller.
     *
     * @var string
     */
    public $controller = ResourceController::class;

    /**
     * The nested resource controller.
     *
     * @var string
     */
    public $nestedController = NestedController::class;

    /**
     * The pivot resource controller.
     *
     * @var string
     */
    public $pivotController = PivotController::class;

    /**
     * The default create request.
     *
     * @var string
     */
    public $create = CreateRequest::class;

    /**
     * The default update request.
     *
     * @var string
     */
    public $update = UpdateRequest::class;

    /**
     * The default api resource.
     *
     * @var string
     */
    public $resource = Resource::class;

    /**
     * The results.
     *
     * @var mixed
     */
    protected $results = [];

    /**
     * The conditions.
     *
     * @var array
     */
    protected $conditions = [];

    /**
     * If request was called.
     *
     * @var bool
     */
    protected $wasRequested = false;

    /**
     * Build the query upon method injection.
     */
    public function validate()
    {
        Validator::make(
            Request::all(),
            $this->getFilterRules()
        )->validate();
    }

    /**
     * Perform the query.
     *
     * @return Collection
     */
    public function results()
    {
        if ($this->wasRequested) {
            return $this->results;
        }

        $this->results = $this
            ->newBuilder()
            ->paginate($this->perPage())
            ->withQueryString();

        $this->validatePagination();

        $this->wasRequested = true;

        return $this->results;
    }

    public function perPage()
    {
        $perPage = Request::input('per_page', app($this->model)->getPerPage());

        if ($perPage > $this->maxPerPage) {
            $perPage = $this->maxPerPage;
        }

        return $perPage;
    }

    /**
     * Get validation rules for url parameters.
     *
     * @var array
     */
    protected function getFilterRules():array
    {
        $rules = [
            'search' => 'nullable|string|min:1|max:64',
            'per_page' => "nullable|integer|min:1,max:{$this->maxPerPage}",
            'sort' => 'nullable|string|in:'.$this->getSortableKeys(),
            'sort-desc' => 'nullable|string|in:'.$this->getSortableKeys(),
        ];

        $rules = array_merge($rules, $this->rulesForIndexing());

        foreach ($this->filters() as $filter) {
            $filter->setRequest(request());
            foreach ($filter->getRules() as $key => $rule) {
                $rules[$key] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Perform the query.
     *
     * @return Builder
     */
    private function newBuilder()
    {
        $this->applyDefaults();

        $query = app($this->model)->query();
        $query = $this->applySort($query);
        $query = $this->applySearch($query);
        $query = $this->applySelect($query);
        $query = $this->applyFilters($query);
        $query = $this->applyConditions($query);

        $this->indexQuery($query);

        return $query;
    }

    /**
     * Apply defaults to the request.
     *
     * @return self
     */
    protected function applyDefaults(): self
    {
        foreach ($this->defaults as $key => $value) {
            if (! Request::has($key)) {
                Request::merge([$key => $value]);
            }
        }

        return $this;
    }

    /**
     * Apply search to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applySearch($query)
    {
        if ($term = Request::input('search')) {
            $query = (new SearchScope())->apply($query, $this->search, $term);
        }

        return $query;
    }

    /**
     * Apply filters to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applyFilters($query)
    {
        foreach ($this->filters() as $filter) {
            $filter->setRequest(request());
            $filter->apply($query);
        }

        return $query;
    }

    /**
     * Apply conditions to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applyConditions($query)
    {
        foreach($this->conditions as $arguments) {
            $query->where(...$arguments);
        }

        return $query;
    }

    /**
     * Apply selects to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applySelect($query)
    {
        if(count($this->select)) {
            return $query->select($this->select);
        }

        return $query;
    }

    /**
     * Apply sort to the query.
     *
     * @var Builder
     * @return Builder
     */
    protected function applySort($query)
    {
        $sortable = $this->getSortable();

        if (empty($sortable)) {
            return $query;
        }

        if (! empty($sortable['relationship'])) {
            $relation = $query->getModel()->{$sortable['relationship']}();

            $query->addSelect([
                $relation->getModel()->getTable().'.'.$relation->getLocalKeyName(), // comments.id
                $relation->getQualifiedForeignKeyName(), // comments.post_id
                $relation->getModel()->getTable().'.'.$sortable['column'], // comments.upvotes
            ]);

            $query->join(
                $relation->getModel()->getTable(),
                $relation->getQualifiedForeignKeyName(),
                '=',
                $relation->getQualifiedParentKeyName()
            );
        } elseif (method_exists($query->getModel(), $sortable['column'])) {
            $query->withCount($sortable['column']);
            $sortable['column'] = $sortable['column'].'_count';
        }

        $query->orderBy($sortable['column'], $sortable['direction']);

        return $query;
    }

    public function getSortable(): array
    {
        $sortable = [
            'relationship' => null,
            'alias' => null,
        ];

        if (Request::filled('sort')) {
            $sortable = [
                'column' => Request::input('sort'),
                'direction'=> 'asc',
            ];
        } elseif (Request::filled('sort-desc')) {
            $sortable = [
                'column' => Request::input('sort-desc'),
                'direction'=> 'desc',
            ];
        }

        if (! isset($sortable['column'])) {
            return [];
        }

        foreach ($this->sort as $definition) {
            if (Str::endsWith($definition, '.'.$sortable['column'])) {
                $sortable['column'] = Str::after($definition, '.');
                $sortable['relationship'] = Str::before($definition, '.');
            } elseif (Str::endsWith($definition, ' as '.$sortable['column'])) {
                $sortable['alias'] = $sortable['column'];
                $sortable['column'] = Str::before($definition, ' as '.$sortable['column']);
                if (Str::contains($sortable['column'], '.')) {
                    $sortable['relationship'] = Str::before($sortable['column'], '.');
                    $sortable['column'] = Str::after($sortable['column'], '.');
                }
            }
        }

        return $sortable;
    }

    public function validatePagination()
    {
        if (Request::input('page') > $this->results->lastPage()) {
            throw new PageExceededException();
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function rulesForIndexing(): array
    {
        return [];
    }

    public function rulesForCreating(): array
    {
        return $this->rules();
    }

    public function rulesForUpdating(): array
    {
        return $this->rules();
    }

    public function filters(): array
    {
        return [];
    }

    public function where(...$arguments): Formation
    {
        $this->conditions[] = $arguments;

        return $this;
    }

    public function whereRelation($relation, $column, $operator, $value): Formation
    {
        return $this->where(function($query) use($relation, $column, $operator, $value) {
            $query->whereRelation($relation, $column, $operator, $value);
        });
    }

    public function nest(Formation $formation, $value): Formation
    {
        $this->where($formation->getForeignKey(), $value);

        return $this;
    }

    public function getForeignKey()
    {
        if($this->foreignKey) {
            return $this->foreignKey;
        }

        return app($this->model)->getForeignKey();
    }

    public function select(array $select): Formation
    {
        $this->select = $select;

        return $this;
    }

    public function options(): Formation
    {
        return $this->select([
            $this->display . ' as display',
            app($this->model)->getKeyName() . ' as value',
        ]);
    }

    public function getSortableKeys()
    {
        $keys = [];

        foreach ($this->sort as $sort) {
            if (Str::contains($sort, ' as ')) {
                $bits = explode(' as ', $sort);
                $keys[] = $bits[1];
            } else {
                if (Str::contains($sort, '.')) {
                    $bits = explode('.', $sort);
                    $keys[] = $bits[1];
                } else {
                    $keys[] = $sort;
                }
            }
        }

        return implode(',', $keys);
    }
}
