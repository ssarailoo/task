<?php

namespace App\Http\Requests;

use App\Enums\TaskStatusEnum;
use App\Rules\UserBelongsToProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['required', 'exists:projects,id'],
            'status' => ['nullable', 'string',Rule::in(TaskStatusEnum::getValues())],
            'due_date' => ['nullable', 'date'],
            'estimated_time' => ['nullable', 'integer', 'min:0'],
            'actual_time' => ['nullable', 'integer', 'min:0'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => [
                'integer',
                'exists:users,id',
                new UserBelongsToProject($this->input('project_id'))
            ],
            'dependencies' => ['nullable', 'array'],
            'dependencies.*' => ['integer', 'exists:tasks,id'],
        ];
    }
}
