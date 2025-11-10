<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'project_id' => $this->project_id,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'estimated_time' => $this->estimated_time,
            'actual_time' => $this->actual_time,

            //TODO : add tag resource
            'project' => new ProjectResource($this->whenLoaded('project')),
            'assigned_users' => UserResource::collection($this->whenLoaded('assignedUsers')),
            'dependencies' => TaskResource::collection($this->whenLoaded('dependencies')),
            'dependents' => TaskResource::collection($this->whenLoaded('dependents')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
