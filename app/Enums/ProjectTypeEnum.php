<?php
namespace App\Enums;

use App\Traits\HasEnumValues;

enum ProjectTypeEnum: string
{
    use HasEnumValues;

    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
}
