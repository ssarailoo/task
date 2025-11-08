<?php
namespace App\Enums;

use App\Traits\HasEnumValues;

enum ProjectPriorityEnum: string
{
    use HasEnumValues;

    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';
}
