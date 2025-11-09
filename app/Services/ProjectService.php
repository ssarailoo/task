<?php

namespace App\Services;

use App\DataTransferObjects\ProjectDTO;
use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
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
use Illuminate\Support\Facades\DB;

 readonly class ProjectService
{
    public function __construct(
        private readonly AttachmentService $attachmentService
    ) {}

    public function getProjects(array $filters): LengthAwarePaginator
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

    public function createProject(ProjectDTO $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = $this->query()->create($data->toArray());
            $this->handleAttachments($project, $data);

            return $project->load('attachments');
        });
    }


    private function handleAttachments(Project $project, ProjectDTO $data): void
    {
        if (empty($projectData->attachments)) {
            return;
        }

        $this->attachmentService->storeAttachments($project, $projectData->attachments);
    }
    private function query(): Builder
    {
        return Project::query();
    }
}
