<?php

namespace App\DataTransferObjects;

use App\Enums\ReportCategoryEnum;
use App\Enums\ReportPeriodEnum;

readonly class ReportDTO extends BaseDTO
{
    public function __construct(
        public int $project_id,
        public ?int $user_id,
        public string $title,
        public ReportCategoryEnum $category,
        public ReportPeriodEnum $period,
        public array $data,
        public ?string $date_from = null,
        public ?string $date_to = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            project_id: $data['project_id'],
            user_id: $data['user_id'] ?? null,
            title: $data['title'],
            category: ReportCategoryEnum::from($data['category']),
            period: ReportPeriodEnum::from($data['period']),
            data: $data['data'],
            date_from: $data['date_from'] ?? null,
            date_to: $data['date_to'] ?? null,
        );
    }
}
