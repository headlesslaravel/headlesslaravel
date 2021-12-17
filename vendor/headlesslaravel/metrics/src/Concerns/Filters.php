<?php

namespace HeadlessLaravel\Metrics\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

trait Filters
{
    public function filter($key): self
    {
        if(is_array($key)) {
            foreach ($key as $k) {
                $this->filter($k);
            }

            return $this;
        }

        $this->builder->when(Request::filled($key), function($query) use($key) {
            $query->where($key, Request::input($key));
        });

        return $this;
    }

    public function filterDates($from = 'from', $to = 'to'): self
    {
        $this->builder->when(Request::filled($from), function($query) use($from, $to) {
            $query->whereBetween($this->dateColumn, [
                Request::input($from),
                Request::input($to, Carbon::now()),
            ]);
        });

        return $this;
    }
}
