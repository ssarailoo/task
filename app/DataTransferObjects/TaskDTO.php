<?php

namespace App\DataTransferObjects;

use App\Enums\TaskStatusEnum;

readonly class TaskDTO extends BaseDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $project_id,
        public TaskStatusEnum $status,
        public ?string $due_date,
        public ?int $estimated_time,
        public ?int $actual_time,
        public ?array $assigned_user_ids ,
        public ?array $dependencies = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            project_id: $data['project_id'],
            status: isset($data['status'])
                ? TaskStatusEnum::from($data['status'])
                : TaskStatusEnum::TODO,
            due_date: $data['due_date'] ?? null,
            estimated_time: $data['estimated_time'] ?? null,
            actual_time: $data['actual_time'] ?? null,
            assigned_user_ids: $data['assigned_user_ids'] ?? null,
            dependencies: $data['dependencies'] ?? null,
        );
    }
}
