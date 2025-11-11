<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    public function handle(Builder $query, Closure $next)
    {
        if (!request()->has($this->filterName())) {
            return $next($query);
        }

        $this->apply($query, request($this->filterName()));

        return $next($query);
    }

    abstract protected function apply(Builder $query, $value);
    abstract protected function filterName(): string;
}
