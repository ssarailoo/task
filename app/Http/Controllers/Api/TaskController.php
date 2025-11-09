<?php

namespace App\Http\Controllers\Api;

use App\DataTransferObjects\TaskDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\IndexTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(IndexTaskRequest $request): JsonResponse
    {
        $tasks = $this->taskService->getTasks($request->validated());

        return response()->json([
            'data' => TaskResource::collection($tasks),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ]
        ],Response::HTTP_OK);
    }
    public function show(Task $task): JsonResponse
    {
        return response()->json(['data' => new TaskResource($task->load(['project', 'assignedUsers', 'dependencies']))], Response::HTTP_OK);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $taskData = TaskDTO::fromRequest($request->validated());
        $task = $this->taskService->createTask($taskData);

        return response()->json(['data' => new TaskResource($task)], Response::HTTP_CREATED);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $taskData = TaskDTO::fromRequest($request->validated());
        $task = $this->taskService->updateTask($task, $taskData);

        return response()->json(['data' => new TaskResource($task)], Response::HTTP_OK);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->deleteTask($task);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
