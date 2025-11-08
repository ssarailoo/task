<?php
namespace App\Enums;

use App\Traits\HasEnumValues;

enum ProjectRecurringEnum: string
{
    use HasEnumValues;

    case NONE = 'none';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}
