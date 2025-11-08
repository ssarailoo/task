<?php

namespace App\Traits;

trait HasEnumValues
{
    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
