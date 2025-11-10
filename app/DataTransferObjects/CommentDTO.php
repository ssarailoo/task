<?php

namespace App\DataTransferObjects;

readonly class CommentDTO extends BaseDTO
{
    public function __construct(
        public int $task_id,
        public int $user_id,
        public string $content,
        public ?int $parent_id = null,
        public ?int $rating = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            task_id: $data['task_id'],
            user_id: $data['user_id'] ?? 1, // TODO: auth()->id()
            content: $data['content'],
            parent_id: $data['parent_id'] ?? null,
            rating: $data['rating'] ?? null,
        );
    }
}
