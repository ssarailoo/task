<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['sometimes', Rule::in(ProjectStatusEnum::getValues())],
            'priority' => ['sometimes', Rule::in(ProjectPriorityEnum::getValues())],
            'type' => ['sometimes', Rule::in(ProjectTypeEnum::getValues())],
            'recurring' => ['sometimes', Rule::in(ProjectRecurringEnum::getValues())],
            'budget' => ['sometimes', 'numeric', 'min:0', 'max:999999999999.99'],
            'created_by' => ['sometimes', 'integer', 'exists:users,id'], // TODO: Remove and use auth()->id()
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
