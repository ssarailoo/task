<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TypeFilter extends Filter
{
    protected function apply(Builder $query, $value)
    {
        return $query->where('type', $value);
    }

    protected function filterName(): string
    {
        return 'type';
    }
}
