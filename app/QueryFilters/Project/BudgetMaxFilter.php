<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class BudgetMaxFilter extends Filter
{
    protected function apply(Builder $query, $value)
    {
        return $query->where('budget', '<=', $value);
    }

    protected function filterName(): string
    {
        return 'budget_max';
    }
}
