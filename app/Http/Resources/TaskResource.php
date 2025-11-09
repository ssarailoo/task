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
            'assigned_users' => $this->assignedUsers->pluck('id'),
            'dependencies' => $this->dependencies->pluck('id'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
