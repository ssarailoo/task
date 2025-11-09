<?php

namespace App\Http\Requests;

use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['sometimes', 'exists:projects,id'],
            'status' => ['nullable', 'string',Rule::in(TaskStatusEnum::getValues())],
            'due_date' => ['nullable', 'date'],
            'estimated_time' => ['nullable', 'integer', 'min:0'],
            'actual_time' => ['nullable', 'integer', 'min:0'],
            'assigned_users' => ['nullable', 'array'],
            'assigned_users.*' => ['integer', 'exists:users,id'],
            'dependencies' => ['nullable', 'array'],
            'dependencies.*' => ['integer', 'exists:tasks,id'],
        ];
    }
}
