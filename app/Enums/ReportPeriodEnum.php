<?php

namespace App\Enums;

use App\Traits\HasEnumValues;

enum ReportPeriodEnum: string
{
    use HasEnumValues;

    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case CUSTOM = 'custom';
}
