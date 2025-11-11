<?php

namespace App\DataTransferObjects;

use App\Enums\TaskStatusEnum;

readonly class UpdatedTaskDTO extends BaseDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?int $project_id = null,
        public ?TaskStatusEnum $status = null,
        public ?string $due_date = null,
        public ?int $estimated_time = null,
        public ?int $actual_time = null,
        public ?array $assigned_user_ids = null,
        public ?array $dependencies = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            project_id: $data['project_id'] ?? null,
            status: isset($data['status']) ? TaskStatusEnum::from($data['status']) : null,
            due_date: $data['due_date'] ?? null,
            estimated_time: $data['estimated_time'] ?? null,
            actual_time: $data['actual_time'] ?? null,
            assigned_user_ids: $data['assigned_user_ids'] ?? null,
            dependencies: $data['dependencies'] ?? null,
        );
    }
}
