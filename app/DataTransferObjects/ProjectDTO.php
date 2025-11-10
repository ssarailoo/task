<?php

namespace App\DataTransferObjects;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;

readonly class ProjectDTO extends BaseDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public string $start_date,
        public string $end_date,
        public ProjectStatusEnum $status,
        public ProjectPriorityEnum $priority,
        public ProjectTypeEnum $type,
        public ProjectRecurringEnum $recurring,
        public float $budget,
        public int $created_by,
        public array $user_ids,
        public ?array $attachments = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            status: isset($data['status'])
                ? ProjectStatusEnum::from($data['status'])
                : ProjectStatusEnum::PENDING,
            priority: isset($data['priority'])
                ? ProjectPriorityEnum::from($data['priority'])
                : ProjectPriorityEnum::LOW,
            type: isset($data['type'])
                ? ProjectTypeEnum::from($data['type'])
                : ProjectTypeEnum::INTERNAL,
            recurring: isset($data['recurring'])
                ? ProjectRecurringEnum::from($data['recurring'])
                : ProjectRecurringEnum::NONE,
            budget: $data['budget'] ?? 0,
            created_by: $data['created_by'] ?? 1, // TODO: auth()->id()
            user_ids: $data['user_ids'],
            attachments: $data['attachments'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        unset($data['user_ids']);

        return $data;
    }
}
