<?php

namespace App\Policies;

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskPolicy
{
    /**
     * Determine if the user can update the task.
     */
    public function update(User $user, Task $task): Response
    {
        return $task->assignedUsers()->where('users.id', $user->id)->exists()
            ? Response::allow()
            : Response::deny('You are not assigned to this task.')->withStatus(HttpResponse::HTTP_FORBIDDEN);
    }

    /**
     * Determine if the task status can be changed to completed.
     */
    public function markAsCompleted(User $user, Task $task): Response
    {

        if (!$task->assignedUsers()->where('users.id', $user->id)->exists()) {
            return Response::deny('You are not assigned to this task.')->withStatus(HttpResponse::HTTP_FORBIDDEN);
        }

        if (!$this->allDependenciesCompleted($task)) {
            $incompleteDependencies = $task->dependencies()
                ->where('status', '!=', TaskStatusEnum::COMPLETED->value)
                ->pluck('title')
                ->toArray();

            $message = 'Cannot mark task as completed. The following dependencies are not yet completed: '
                . implode(', ', $incompleteDependencies);

            return Response::deny($message)->withStatus(HttpResponse::HTTP_FORBIDDEN);;;
        }

        return Response::allow();
    }

    /**
     * Check if all task dependencies are completed.
     */
    private function allDependenciesCompleted(Task $task): bool
    {
        $dependencies = $task->dependencies;

        if ($dependencies->isEmpty()) {
            return true;
        }

        return $dependencies->every(function (Task $dependency) {
            return $dependency->status === TaskStatusEnum::COMPLETED->value;
        });
    }
}
