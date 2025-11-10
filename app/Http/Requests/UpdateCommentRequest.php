<?php

namespace App\Http\Requests;

use App\Rules\NoRatingForReplies;
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
        $comment = $this->route('comment');
        $parentId = $comment?->parent_id;
        return [
            'content' => ['required', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5', new NoRatingForReplies($parentId)],
        ];
    }
}
