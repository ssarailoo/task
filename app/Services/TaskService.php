<?php

namespace App\Services;

use App\DataTransferObjects\TaskDTO;
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

            if ($data->assigned_users) {
                $task->assignedUsers()->sync($data->assigned_users);
            }

            if ($data->dependencies) {
                $task->dependencies()->sync($data->dependencies);
            }

            return $task->fresh(['project', 'assignedUsers', 'dependencies']);
        });
    }

    public function updateTask(Task $task, TaskDTO $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update($data->toArray());

            if ($data->assigned_users !== null) {
                $task->assignedUsers()->sync($data->assigned_users);
            }

            if ($data->dependencies !== null) {
                $task->dependencies()->sync($data->dependencies);
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
