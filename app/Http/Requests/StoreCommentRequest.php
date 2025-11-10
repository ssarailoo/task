<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'content' => ['required', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function withValidator($validator): void
    {
         //TODO : add custom rule
            $validator->after(function ($validator) {
                $isReply = $this->route('comment') !== null;
                $hasRating = $this->filled('rating');
                if ($isReply && $hasRating) {
                    $validator->errors()->add('rating', 'Replies cannot have ratings.');
                }
            });
    }
}
