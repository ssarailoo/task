<?php

namespace App\DataTransferObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

abstract readonly class BaseDTO implements Arrayable, JsonSerializable
{
    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];

        foreach ($properties as $property) {
            $value = $property->getValue($this);

            if ($value instanceof \BackedEnum) {
                $data[$property->getName()] = $value->value;
                continue;
            }

            if ($value instanceof self) {
                $data[$property->getName()] = $value->toArray();
                continue;
            }

            if (is_array($value)) {
                $data[$property->getName()] = array_map(
                    fn($item) => $item instanceof self ? $item->toArray() : $item,
                    $value
                );
                continue;
            }

            $data[$property->getName()] = $value;
        }

        return $data;
    }

    /**
     * Convert DTO to JSON
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Create DTO from array
     */
    abstract public static function fromRequest(array $data): static;
}
