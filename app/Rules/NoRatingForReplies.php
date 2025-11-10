<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoRatingForReplies implements ValidationRule
{
    public function __construct(
        private readonly ?int $parentId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->parentId !== null && $value !== null) {
            $fail('Replies cannot have ratings.');
        }
    }
}
