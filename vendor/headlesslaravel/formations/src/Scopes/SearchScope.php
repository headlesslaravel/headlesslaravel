<?php

namespace HeadlessLaravel\Formations\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchScope
{
    /**
     * @param Builder $query
     * @param array $columns
     * @param string $term
     * @return Builder
     */
    public function apply($query, $columns, $term)
    {
        return $query->where(function ($query) use ($columns, $term) {
            foreach ($columns as $column) {
                if (Str::contains($column, '.')) {
                    $column = explode('.', $column);
                    $query->orWhereHas($column[0], function ($query) use ($column, $term) {
                        $query->where($column[1], 'LIKE', "%${term}%");
                    });
                } else {
                    $query->orWhere($column, 'LIKE', "%${term}%");
                }
            }
        });
    }
}
