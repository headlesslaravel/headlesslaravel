<?php

namespace HeadlessLaravel\Formations\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasQueries
{
    /**
     * Adjust index method query.
     *
     * @param Builder $query
     */
    public function indexQuery(Builder $query)
    {
        //
    }

    /**
     * Adjust show method query.
     *
     * @param Builder $query
     */
    public function showQuery(Builder $query)
    {
        //
    }

    /**
     * Adjust edit method query.
     *
     * @param Builder $query
     */
    public function editQuery(Builder $query)
    {
        //
    }

    /**
     * Adjust update method query.
     *
     * @param Builder $query
     */
    public function updateQuery(Builder $query)
    {
        //
    }

    /**
     * Adjust restore method query.
     *
     * @param Builder $query
     */
    public function restoreQuery(Builder $query)
    {
        //
    }

    /**
     * Adjust destroy method query.
     *
     * @param Builder $query
     */
    public function destroyQuery(Builder $query)
    {
        //
    }

    /**
     * Adjust force delete method query.
     *
     * @param Builder $query
     */
    public function forceDeleteQuery(Builder $query)
    {
        //
    }

    /**
     * Call the proper query callback.
     *
     * @param string $method
     * @param Builder $query
     */
    public function queryCallback(string $method, Builder $query)
    {
        $method = $method . 'Query';

        $this->$method($query);
    }
}
