<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RecurringFilter extends Filter
{
    protected function apply(Builder $query, $value)
    {
        return $query->where('recurring', $value);
    }

    protected function filterName(): string
    {
        return 'recurring';
    }
}
