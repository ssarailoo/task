<?php

namespace App\Services;

use App\DataTransferObjects\TaskDTO;
use App\DataTransferObjects\UpdatedTaskDTO;
use App\DataTransferObjects\UpdateTaskDTO;
use App\Enums\TaskStatusEnum;
use App\Events\TaskCompleted;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

readonly class TaskService
{
    public function getTasks(array $filters): LengthAwarePaginator
    {
        return $this->query()->paginate($filters['per_page'] ?? 15);
    }

    public function createTask(TaskDTO $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = $this->query()->create($data->toArray());

            if ($data->assigned_user_ids) {
                $task->assignedUsers()->sync($data->assigned_user_ids);
            }

            if ($data->dependencies) {
                $task->dependencies()->sync($data->dependencies);
            }

            return $task->fresh(['project', 'assignedUsers', 'dependencies']);
        });
    }

    public function updateTask(Task $task, UpdatedTaskDTO $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $wasCompleted = $task->status === TaskStatusEnum::COMPLETED->value;
            $isNowCompleted = $data->status === TaskStatusEnum::COMPLETED;

            $updateData = array_filter($data->toArray(), fn($value) => $value !== null);
            $task->update($updateData);

            if ($data->assigned_user_ids !== null) {
                $task->assignedUsers()->sync($data->assigned_user_ids);
            }

            if ($data->dependencies !== null) {
                $task->dependencies()->sync($data->dependencies);
            }

            if (!$wasCompleted && $isNowCompleted) {
                event(new TaskCompleted($task));
            }
            return $task->fresh(['project', 'assignedUsers', 'dependencies']);
        });
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }

    private function query(): Builder
    {
        return Task::query();
    }
}
