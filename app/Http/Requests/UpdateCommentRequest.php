<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Check if user owns this comment
        return true;
    }

    public function rules(): array
    {
        return [
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
