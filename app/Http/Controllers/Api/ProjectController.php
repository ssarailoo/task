<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{

    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function index(IndexProjectRequest $request): JsonResponse
    {
        $projects = $this->projectService->getProjects($request->validated());

        return response()->json([
            'data' => ProjectResource::collection($projects),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ]
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->projectService->createProject($request->validated());
            return response()->json(['data' => new ProjectResource($project)], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            //TODO: add metrics and logs
            return response()->json([
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



}
