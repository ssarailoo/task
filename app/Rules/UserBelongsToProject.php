<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

readonly class UserBelongsToProject implements ValidationRule
{
    public function __construct(
        private ?int $projectId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->projectId === null) {
            return;
        }

        $exists = DB::table('project_user')
            ->where('project_id', $this->projectId)
            ->where('user_id', $value)
            ->exists();

        if (!$exists) {
            $fail('The selected user must be a member of the project.');
        }
    }
}
