<?php

namespace App\DataTransferObjects;

readonly class CommentDTO extends BaseDTO
{
    public function __construct(
        public ?int $task_id = null,
        public ?int $user_id = null,
        public ?string $content = null,
        public ?int $parent_id = null,
        public ?int $rating = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            task_id: $data['task_id'] ?? null,
            user_id:  auth()->id(),
            content: $data['content'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            rating: $data['rating'] ?? null,
        );
    }
}
