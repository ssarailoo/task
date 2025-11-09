<?php

namespace App\Enums;

use App\Traits\HasEnumValues;

enum TaskStatusEnum: string
{
    use HasEnumValues;

    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case IN_REVIEW = 'in_review';
    case BLOCKED = 'blocked';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
