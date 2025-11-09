<?php

namespace App\Services;

use App\Models\Project;
use App\QueryFilters\Project\BudgetMaxFilter;
use App\QueryFilters\Project\BudgetMinFilter;
use App\QueryFilters\Project\CreatedByFilter;
use App\QueryFilters\Project\PriorityFilter;
use App\QueryFilters\Project\RecurringFilter;
use App\QueryFilters\Project\SearchFilter;
use App\QueryFilters\Project\StartDateFromFilter;
use App\QueryFilters\Project\StartDateToFilter;
use App\QueryFilters\Project\StatusFilter;
use App\QueryFilters\Project\TypeFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class ProjectService
{
    public function getAllProjects(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        $projects = app(Pipeline::class)
            ->send($query)
            ->through([
                StatusFilter::class,
                PriorityFilter::class,
                TypeFilter::class,
                RecurringFilter::class,
                CreatedByFilter::class,
                StartDateFromFilter::class,
                StartDateToFilter::class,
                BudgetMinFilter::class,
                BudgetMaxFilter::class,
                SearchFilter::class,
            ])
            ->thenReturn();

        return $projects->paginate($filters['per_page'] ?? 15);
    }

    private function query(): Builder
    {
        return Project::query();
    }
}
