<?php

namespace App\Traits;

trait HasOptions
{
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $enum) {
            $options[$enum->value] = $enum->displayName();
        }

        return $options;
    }

    abstract public function displayName(): string;
}
