<?php

namespace App\Enums;

use App\Traits\HasOptions;

enum VideoStatus: string
{
    use HasOptions;

    case QUEUED_ANALYSING = 'QUEUED_ANALYSING';
    case QUEUED_ENCODING = 'QUEUED_ENCODING';
    case NOT_ANALYSED = 'NOT_ANALYSED';
    case NOT_PLAYABLE_NOT_ENCODED = 'NOT_PLAYABLE_NOT_ENCODED';
    case NOT_PLAYABLE_ENCODED = 'NOT_PLAYABLE_ENCODED';
    case PLAYABLE_NOT_ENCODED = 'PLAYABLE_NOT_ENCODED';
    case PLAYABLE_ENCODED = 'PLAYABLE_ENCODED';

    public function displayName(): string
    {
        return match ($this) {
            self::QUEUED_ANALYSING => 'Queued (Analysing)',
            self::QUEUED_ENCODING => 'Queued (Encoding)',
            self::NOT_ANALYSED => 'Not analysed',
            self::NOT_PLAYABLE_NOT_ENCODED => 'Not playable (Not encoded)',
            self::NOT_PLAYABLE_ENCODED => 'Not playable (Encoded)',
            self::PLAYABLE_NOT_ENCODED => 'Playable (Not encoded)',
            self::PLAYABLE_ENCODED => 'Playable (Encoded)',
        };
    }
}
