<?php

namespace HeadlessLaravel\Formations;

use HeadlessLaravel\Formations\Exceptions\ReservedException;
use HeadlessLaravel\Formations\Exceptions\UnauthorizedException;
use HeadlessLaravel\Formations\Scopes\SearchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Filter
{
    /**
     * The query string key.
     *
     * @var
     */
    public $publicKey;

    /**
     * The internal key.
     *
     * @var
     */
    protected $key;

    /**
     * The validation rules.
     *
     * @var array
     */
    public $rules = [];

    /**
     * The query callbacks.
     *
     * @var array
     */
    public $queries = [];

    /**
     * The conditionals query callbacks.
     *
     * @var array
     */
    protected $conditionals = [];

    /**
     * The query parameter suffixes.
     *
     * @var array
     */
    protected $modifiers = [];

    /**
     * The active modifier(s).
     *
     * @var
     */
    protected $modifier;

    /**
     * The active value(s).
     *
     * @var
     */
    protected $value;

    /**
     * The active query.
     *
     * @var
     */
    protected $query;

    /**
     * The active request.
     *
     * @var
     */
    protected $request;

    /**
     * The query requires auth.
     *
     * @var
     */
    protected $authenticated = false;

    /**
     * The query handles multiple.
     *
     * @var
     */
    public $multiple = false;

    /**
     * The value is in cents.
     *
     * @var
     */
    public $cents = false;

    /**
     * The query method was called.
     *
     * @var
     */
    public $filterMethodCalled = false;

    /**
     * Make a filter instance.
     *
     * @param $public
     * @param $internal
     * @return Filter
     */
    public static function make($public, $internal = null)
    {
        if (in_array($public, ['search', 'per_page', 'sort', 'sort-desc'])) {
            throw new ReservedException();
        }

        return (new self)->init($public, $internal);
    }

    /**
     * Add the filter key.
     *
     * @param $public
     * @param $internal
     * @return $this
     */
    protected function init($public, $internal = null)
    {
        $this->publicKey = $public;

        $this->key = is_null($internal) ? $public : $internal;

        return $this;
    }

    /**
     * Add the public filter key.
     *
     * @param $value
     * @param $callback
     * @return $this
     */
    public function when($value, $callback)
    {
        $this->conditionals[$value] = $callback;

        return $this;
    }

    /**
     * Make a boolean filter type.
     *
     * @return $this
     */
    public function boolean()
    {
        $this->withRules('nullable|in:true,false');

        $this->withQuery(function ($query) {
            $query->where(
                $this->key,
                $this->resolveBoolean($this->value)
            );
        });

        return $this;
    }

    /**
     * Make a toggle filter type.
     *
     * @return $this
     */
    public function toggle()
    {
        $this->withRules('nullable|in:true');

        $this->withQuery(function ($query) {
            $query->where(
                $this->key,
                $this->resolveBoolean($this->value)
            );
        });

        return $this;
    }

    /**
     * Make a option filter type.
     *
     * @return $this
     */
    public function options(array $options)
    {
        $this->withRules('nullable|in:'.implode(',', $options));

        $this->withQuery(function ($query) {
            $query->whereIn($this->key, Arr::wrap($this->value));
        });

        return $this;
    }

    /**
     * Make a scope filter type.
     *
     * @return $this
     */
    public function scope($scope = null)
    {
        $this->withRules('nullable');

        $scope = $scope ?? $this->key;

        $this->withQuery(function ($query) use ($scope) {
            $query->scopes([$scope => $this->value]);
        });

        return $this;
    }

    /**
     * Make a boolean scope filter type.
     *
     * @return $this
     */
    public function scopeBoolean($scope = null)
    {
        $this->withRules('nullable|in:true,false');

        $scope = $scope ?? $this->key;

        $this->withQuery(function ($query) use ($scope) {
            $value = $this->resolveBoolean($this->value);
            if ($value) {
                $query->scopes([$scope => $value]);
            } else {
                $model = $query->getModel();
                $query->whereNotIn($model->getKeyName(), function ($q) use ($model, $scope, $value) {
                    $q->select($model->getKeyName())
                        ->from($model->getTable());

                    $model->callNamedScope($scope, [$q, $value]);
                });
            }
        });

        return $this;
    }

    /**
     * Make a search filter type.
     *
     * @return $this
     */
    public function search($columns)
    {
        $this->withRules('nullable|string|min:1|max:64');

        $this->withQuery(function ($query) use ($columns) {
            (new SearchScope())->apply($query, $columns, $this->value);
        });

        return $this;
    }

    /**
     * Make a range filter type.
     *
     * @return $this
     */
    public function range()
    {
        $this->withNumericMinMax();

        $this->withQuery(function ($query) {
            if (isset($this->value['min'], $this->value['max'])) {
                $query->whereBetween($this->key, [$this->value['min'], $this->value['max']]);
            } elseif (isset($this->value['min'])) {
                $query->where($this->key, '>=', $this->value['min']);
            } elseif (isset($this->value['max'])) {
                $query->where($this->key, '<=', $this->value['max']);
            }
        });

        return $this;
    }

    /**
     * Make a between filter type.
     *
     * @return $this
     */
    public function between($name, array $range)
    {
        $this->withQuery(function ($query) use($name, $range) {
            if ($this->value === $name) {
                sort($range);
                $query->whereBetween($this->key, $range);
            }
        });

        return $this;
    }

    /**
     * Make a date filter type.
     *
     * @return $this
     */
    public function date()
    {
        $this->withRules('nullable|date');

        $this->withQuery(function ($query) {
            $query->where(function ($query) {
                foreach (Arr::wrap($this->value) as $value) {
                    $query->orWhereDate($this->key, Carbon::parse($value));
                }
            });
        });

        return $this;
    }

    /**
     * Make a date range filter type.
     *
     * @return $this
     */
    public function dateRange()
    {
        $this->modifier('min');
        $this->modifier('max');

        $this->withRules('nullable|date', "$this->publicKey:max");
        $this->withRules('nullable|date', "$this->publicKey:min");

        $this->withQuery(function ($query) {
            if (isset($this->value['min'], $this->value['max'])) {
                $query->whereBetween($this->key, [
                    Carbon::parse($this->value['min']),
                    Carbon::parse($this->value['max']),
                ]);
            } elseif (isset($this->value['min'])) {
                $query->whereDate($this->key, '>=', Carbon::parse($this->value['min']));
            } elseif (isset($this->value['max'])) {
                $query->whereDate($this->key, '<=', Carbon::parse($this->value['max']));
            }
        });

        return $this;
    }

    /**
     * Make a relationship exists filter type.
     *
     * @param null $relationship
     * @return $this
     */
    public function exists($relationship = null)
    {
        $this->modifier('exists');

        $this->withRules('nullable|in:true,false');

        if (is_null($relationship)) {
            $relationship = $this->key;
        }

        $this->withQuery(function ($query) use ($relationship) {
            $this->value['exists'] === 'true'
                ? $query->has($relationship)
                : $query->doesntHave($relationship);
        });

        return $this;
    }

    /**
     * Make a relationship count filter type.
     *
     * @return $this
     */
    public function count()
    {
        $this->modifier('count');

        $this->withRules('nullable|numeric');

        $this->withQuery(function ($query) {
            $query->has($this->key, '=', $this->value['count']);
        });

        return $this;
    }

    /**
     * Make a relationship count range filter type.
     *
     * @return $this
     */
    public function countRange()
    {
        $this->withNumericMinMax();

        $this->withQuery(function ($query) {
            $modifiers = [
                'min' => '>=', 'max' => '<=',
            ];

            foreach ($this->modifier as $modifier) {
                if ($value = Arr::get($this->value, $modifier)) {
                    $query->has($this->key, $modifiers[$modifier], $value);
                }
            }
        });

        return $this;
    }

    /**
     * Make a related relationship type.
     *
     * @return $this
     */
    public function related()
    {
        $this->withRules('nullable');

        $this->withQuery(function ($query) {

            $this->validateMultiple();

            $relation = $query->getModel()->{$this->key}();

            if(is_a($relation, BelongsTo::class)) {
                $key = $relation->getForeignKeyName();
                $query->whereIn($key, Arr::wrap($this->value));
                return $query;
            }

            $query->whereHas($this->key, function($query) use($relation) {
                $query->whereIn(
                    $relation->getQualifiedRelatedKeyName(),
                    Arr::wrap($this->value)
                );
            });

            return $query;
        });

        return $this;
    }

    /**
     * Make a withTrashed filter.
     *
     * @return $this
     */
    public function withTrashed()
    {
        $this->withRules('nullable|in:true');

        $this->withQuery(function ($query) {
            $query->withTrashed();
        });

        return $this;
    }

    /**
     * Make a onlyTrashed filter.
     *
     * @return $this
     */
    public function onlyTrashed()
    {
        $this->withRules('nullable|in:true');

        $this->withQuery(function ($query) {
            $query->onlyTrashed();
        });

        return $this;
    }

    /**
     * Make a radius filter.
     *
     * @return $this
     */
    public static function radius($maxDistance = 100)
    {
        $filter = (new self)->make(['latitude', 'longitude', 'distance']);

        $filter->withRules(['numeric', 'required_with:longitude,latitude', "lte:$maxDistance"], 'distance');
        $filter->withRules(['numeric', 'required_with:latitude,distance'], 'longitude');
        $filter->withRules(['numeric', 'required_with:longitude,distance'], 'latitude');

        $filter->withQuery(function ($query) use($filter) {
            $distance = $filter->value['distance'];
            $method = 'ST_Distance_Sphere(Point(longitude, latitude), Point(?, ?))';
            $conversion = '* 0.000621371192';

            $query->whereRaw("$method $conversion < $distance", [
                $filter->value['longitude'],
                $filter->value['latitude'],
            ]);
        });

        return $filter;
    }

    /**
     * Make a bounds filter.
     *
     * @return $this
     */
    public static function bounds()
    {
        $filter = (new self)->make(['sw_lat', 'sw_lng', 'ne_lat', 'ne_lng']);

        $filter->withRules(['numeric', 'required_with:sw_lng,ne_lat,ne_lng'], 'sw_lat');
        $filter->withRules(['numeric', 'required_with:sw_lat,ne_lat,ne_lng'], 'sw_lng');
        $filter->withRules(['numeric', 'required_with:sw_lat,sw_lng,ne_lng'], 'ne_lat');
        $filter->withRules(['numeric', 'required_with:sw_lat,sw_lng,ne_lat'], 'ne_lng');

        $filter->withQuery(function ($query) use($filter) {

            $range = ($filter->value['sw_lat'] < $filter->value['ne_lat'])
                ? [ $filter->value['sw_lat'], $filter->value['ne_lat'] ]
                : [ $filter->value['ne_lat'], $filter->value['sw_lat'] ];

            $query->whereBetween('latitude', $range);

            $range = ($filter->value['sw_lng'] < $filter->value['ne_lng'])
                ? [ $filter->value['sw_lng'], $filter->value['ne_lng'] ]
                : [ $filter->value['ne_lng'], $filter->value['sw_lng'] ];

            $query->whereBetween('longitude', $range);

        });

        return $filter;
    }

    /**
     * Register a modifier.
     *
     * @param $modifier
     * @return $this
     */
    public function modifier($modifier)
    {
        $this->modifiers[] = $modifier;

        return $this;
    }

    /**
     * Set rules for current filter.
     *
     * @param null $rules
     * @param null $modifier
     * @return $this|array
     */
    public function rules($rules, $modifier = null)
    {
        if (! is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $this->rules = function () use ($rules, $modifier) {
            $key = $modifier ? "{$this->publicKey}:${modifier}" : $this->publicKey;

            return [$key => $rules];
        };

        return $this;
    }

    /**
     * Append rules for current filter.
     *
     * @param null $rules
     * @param null $modifier
     * @return $this|array
     */
    public function withRules($rules, $key = null)
    {
        if (! is_array($rules)) {
            $rules = explode('|', $rules);
        }

        $this->rules[] = function () use ($rules, $key) {
            $key = is_null($key) ? $this->publicKey : $key;

            return  [$key => $rules];
        };

        return $this;
    }

    /**
     * Get the rules flattened.
     *
     * @return $this|array
     */
    public function getRules()
    {
        $output = [];

        $key = $this->publicKey;

        $isMultiple = ($this->multiple && is_array($this->request->get($key)));

        if (is_callable($this->rules)) {
            $method = $this->rules;

            return $method();
        }

        foreach ($this->rules as $callback) {
            $rules = $callback();
            $key = array_keys($rules)[0];
            $rules = $rules[$key];
            if ($isMultiple) {
                $key = "${key}.*";
            }

            $output[$key] = array_merge(Arr::get($output, $key, []), $rules);
        }

        return $output;
    }

    /**
     * Redirect login if not authenticated user.
     *
     * @return $this
     */
    public function auth()
    {
        $this->authenticated = true;

        return $this;
    }

    /**
     * Allow multiple values for same key.
     *
     * @return $this
     */
    public function multiple()
    {
        $this->multiple = true;

        return $this;
    }

    /**
     * Convert dollars (100) into cents (10000).
     *
     * @return $this
     */
    public function asCents()
    {
        $this->cents = true;

        return $this;
    }

    /**
     * Set the query callback.
     *
     * @param $callback
     * @return $this
     */
    public function query($callback)
    {
        $this->filterMethodCalled = true;

        $this->queries = [$callback];

        return $this;
    }

    /**
     * Add a callback to the query builder.
     *
     * @param $callback
     * @return $this
     */
    public function withQuery($callback)
    {
        $this->filterMethodCalled = true;

        $this->queries[] = $callback;

        return $this;
    }

    /**
     * Apply the filters to the query.
     *
     * @param $query
     */
    public function apply($query)
    {
        $this->prepare();

        if (empty($this->value)) {
            return;
        }

        if ($this->authenticated && ! auth()->check()) {
            throw new UnauthorizedException();
        }

        if (! $this->filterMethodCalled) {
            $this->query = $this->defaultQueryCallback($query);
            $this->applyConditionals($query);

            return;
        }

        foreach ($this->queries as $callback) {
            $callback($query);
        }

        $this->applyConditionals($query);

        $this->query = $query;
    }

    /**
     * Get values from the request.
     */
    protected function prepare()
    {
        $keys = [];

        if (is_array($this->publicKey)) {
            $keys = $this->publicKey;
        } elseif (count($this->modifiers)) {
            foreach ($this->modifiers as $modifier) {
                $keys[] = "{$this->publicKey}:${modifier}";
            }
        } else {
            $keys = [$this->publicKey];
        }

        $parameters = $this->request->only($keys);

        if (count($parameters) == 0) {
            return; // don't set value & operator
        }

        if (is_array($this->publicKey)) {
            $this->value = $parameters;
            return;
        }

        if (count($this->modifiers) == 0 && count($parameters) == 1) {
            $this->modifier = array_keys($parameters)[0];
            $this->value = array_values($parameters)[0];
            $this->value = $this->applyCents($this->value);
            return; // no array values needed because its a single value
        }

        foreach ($parameters as $key => $value) {
            $modifier = str_replace("{$this->publicKey}:", '', $key);
            $this->modifier[] = $modifier;
            $this->value[$modifier] = $value;
        }

        $this->value = $this->applyCents($this->value);
    }

    /**
     * Apply default where.
     *
     * @param $query
     * @return Builder;
     */
    protected function defaultQueryCallback($query)
    {
        $this->validateMultiple();

        return $query->whereIn($this->key, Arr::wrap($this->value));
    }

    /**
     * Apply conditionals.
     *
     * @param $query
     * @return Builder;
     */
    protected function applyConditionals($query)
    {
        foreach ($this->conditionals as $value => $callback) {
            if (in_array($value, Arr::wrap($this->value))) {
                $callback($query);
            }
        }

        return $query;
    }

    /**
     * Apply cents.
     *
     * @param $query
     * @return Builder;
     */
    protected function applyCents($value)
    {
        if(!$this->cents) {
            return $value;
        }

        if(!is_array($value)) {
            return $value * 100;
        }

        foreach($value as $k => $v) {
            $value[$k] = $v * 100;
        }

        return $value;
    }

    /**
     * Validate multiple.
     */
    protected function validateMultiple()
    {
        if (! $this->multiple && is_array($this->value)) {
            throw ValidationException::withMessages([
                $this->publicKey => 'Multiple not permitted.',
            ]);
        }
    }

    /**
     * Convert truthy / falsy values.
     *
     * @param $value
     * @return int
     */
    public function resolveBoolean($value)
    {
        return ['false' => 0, 'true' => 1][$value];
    }

    /**
     * Set the request.
     *
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Adds min max modifier and validation.
     */
    public function withNumericMinMax(): void
    {
        $this->modifier('min');
        $this->modifier('max');

        $minRules = ['nullable', 'numeric', function ($attribute, $value, $fail) {
            if ($this->request->filled("{$this->publicKey}:max")
                && $value > $this->request->input("{$this->publicKey}:max")) {
                $fail('Must be less than max.');
            }
        }];

        $maxRules = ['nullable', 'numeric', function ($attribute, $value, $fail) {
            if ($this->request->filled("{$this->publicKey}:min")
                && $value < $this->request->input("{$this->publicKey}:min")) {
                $fail('Must be greater than min.');
            }
        }];

        $this->withRules($minRules, "$this->publicKey:min");
        $this->withRules($maxRules, "$this->publicKey:max");
    }
}
