<?php

namespace App\Http\Requests;

use App\Rules\NoRatingForReplies;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $parentId = $this->route('comment')?->id;
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'content' => ['required', 'string', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5',new NoRatingForReplies($parentId)],
        ];
    }


}
