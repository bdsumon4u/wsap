<?php

namespace App\Enums\Traits;

trait ToArray
{
    public static function toArray(string $prop): array
    {
        return array_map(fn ($item) => $item->{$prop}, static::cases());
    }

    public static function names(): array
    {
        return static::toArray('name');
    }

    public static function values(): array
    {
        return static::toArray('value');
    }
}
