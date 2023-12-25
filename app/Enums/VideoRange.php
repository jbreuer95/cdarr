<?php

namespace App\Enums;

use App\Traits\HasOptions;

enum VideoRange:string
{
    use HasOptions;

    case SDR = 'SDR';
    case HDR = 'HDR';

    public function displayName(): string
    {
        return match ($this) {
            self::SDR => 'Standard Dynamic Range',
            self::HDR => 'High dynamic range',
        };
    }
}
