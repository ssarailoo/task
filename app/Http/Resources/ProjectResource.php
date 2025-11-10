<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'recurring' => $this->recurring,
            'budget' => $this->budget,
            'created_by' => $this->created_by,

            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'reports' => ReportResource::collection($this->whenLoaded('reports')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,


        ];
    }
}
