<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter extends Filter
{
    protected function apply(Builder $query, $value): Builder
    {
        return $query->where('status', $value);
    }

    protected function filterName(): string
    {
        return 'status';
    }
}
