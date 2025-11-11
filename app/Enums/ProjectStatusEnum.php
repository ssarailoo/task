<?php

namespace App\Enums;

use App\Traits\HasEnumValues;

enum ProjectStatusEnum : string
{
use HasEnumValues;
case PENDING = 'pending';
case IN_PROGRESS = 'in_progress';
case COMPLETED = 'completed';
case ON_HOLD = 'on_hold';
case CANCELLED = 'cancelled';

}
