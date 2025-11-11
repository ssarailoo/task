<?php

namespace App\QueryFilters\Project;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter extends Filter
{
    // TODO: Add full-text search index on title and description columns for better performance
    protected function apply(Builder $query, $value)
    {
        return $query->where(function($q) use ($value) {
            $q->where('title', 'like', "%{$value}%")
                ->orWhere('description', 'like', "%{$value}%");
        });
    }

    protected function filterName(): string
    {
        return 'search';
    }
}
