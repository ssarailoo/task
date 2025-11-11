<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PriorityFilter extends Filter
{
    protected function apply(Builder $query, $value)
    {
        return $query->where('priority', $value);
    }

    protected function filterName(): string
    {
        return 'priority';
    }
}
