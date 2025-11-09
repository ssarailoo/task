<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CreatedByFilter extends Filter
{
    protected function apply(Builder $query, $value)
    {
        return $query->where('created_by', $value);
    }

    protected function filterName(): string
    {
        return 'created_by';
    }
}
