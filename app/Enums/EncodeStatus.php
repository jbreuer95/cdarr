<?php

namespace App\Enums;

use App\Traits\HasOptions;

enum EncodeStatus:string
{
    use HasOptions;

    case WAITING = 'WAITING';
    case TRANSCODING = 'TRANSCODING';
    case FINISHED = 'FINISHED';
    case FAILED = 'FAILED';

    public function displayName(): string
    {
        return match ($this) {
            self::WAITING => 'Waiting',
            self::TRANSCODING => 'Encoding',
            self::FINISHED => 'Finished',
            self::FAILED => 'Failed',
        };
    }
}
