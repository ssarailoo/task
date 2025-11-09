<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{

        public function getAllProjects(array $filters): LengthAwarePaginator
        {
        return $this->query()
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority', $priority))
            ->when($filters['type'] ?? null, fn($q, $type) => $q->where('type', $type))
            ->when($filters['recurring'] ?? null, fn($q, $recurring) => $q->where('recurring', $recurring))
            ->when($filters['created_by'] ?? null, fn($q, $userId) => $q->where('created_by', $userId))
            ->when($filters['start_date_from'] ?? null, fn($q, $date) => $q->where('start_date', '>=', $date))
            ->when($filters['start_date_to'] ?? null, fn($q, $date) => $q->where('start_date', '<=', $date))
            ->when($filters['budget_min'] ?? null, fn($q, $budget) => $q->where('budget', '>=', $budget))
            ->when($filters['budget_max'] ?? null, fn($q, $budget) => $q->where('budget', '<=', $budget))
            ->when($filters['search'] ?? null, fn($q, $search) =>
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
            )
            ->paginate($filters['per_page'] ?? 15);
    }

    private function query(): Builder
    {
       return Project::query();
    }
}
