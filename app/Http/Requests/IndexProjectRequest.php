<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(ProjectStatusEnum::getValues())],
            'priority' => ['sometimes', Rule::in(ProjectPriorityEnum::getValues())],
            'type' => ['sometimes', Rule::in(ProjectTypeEnum::getValues())],
            'recurring' => ['sometimes', Rule::in(ProjectRecurringEnum::getValues())],
            'created_by' => ['sometimes', 'integer', 'exists:users,id'],
            'start_date_from' => ['sometimes', 'date'],
            'start_date_to' => ['sometimes', 'date', 'after_or_equal:start_date_from'],
            'budget_min' => ['sometimes', 'numeric', 'min:0'],
            'budget_max' => ['sometimes', 'numeric', 'min:0', 'gte:budget_min'],
            'search' => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
