<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StartDateToFilter extends Filter
{
    protected function apply(Builder $query, $value)
    {
        return $query->where('start_date', '<=', $value);
    }

    protected function filterName(): string
    {
        return 'start_date_to';
    }
}
