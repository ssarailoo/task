<?php

namespace App\Enums;

use App\Traits\HasEnumValues;

enum RoleEnum :string
{
    use HasEnumValues;

    case EXPERT = 'expert';
    case SUPERVISOR = 'supervisor';
    case MANAGER = 'manager';

}
