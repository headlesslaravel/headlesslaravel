<?php

namespace HeadlessLaravel\Metrics\Concerns;

use Illuminate\Support\Collection;

trait Aggregates
{
    public function aggregate(string $column, string $aggregate):mixed
    {
        $this->fallbacks();

        $builder = $this->builder
            ->toBase()
            ->whereBetween($this->dateColumn, [$this->from, $this->to]);

        if(! $this->interval) {
            return $builder->aggregate($aggregate, [$column]);
        }

        $values = $builder
            ->selectRaw("
                {$this->getSqlDate()} as date,
                {$aggregate}({$column}) as aggregate
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->mapValuesToDates($values);
    }

    public function average(string $column):mixed
    {
        return $this->aggregate($column, 'avg');
    }

    public function min(string $column):mixed
    {
        return $this->aggregate($column, 'min');
    }

    public function max(string $column):mixed
    {
        return $this->aggregate($column, 'max');
    }

    public function sum(string $column):mixed
    {
        return $this->aggregate($column, 'sum');
    }

    public function count(string $column = '*'):mixed
    {
        return $this->aggregate($column, 'count');
    }
}
