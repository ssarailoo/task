<?php

namespace App\Enums;

use App\Traits\HasEnumValues;

enum ReportCategoryEnum: string
{
    use HasEnumValues;

    case TEAM_PERFORMANCE = 'team_performance';
    case TIME_PREDICTION = 'time_prediction';
    case PROJECT_STATUS = 'project_status';
    case TASK_COMPLETION = 'task_completion';
}
