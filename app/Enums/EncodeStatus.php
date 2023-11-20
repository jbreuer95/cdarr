<?php

namespace App\Enums;

enum EncodeStatus:string
{
    case WAITING = 'WAITING';
    case TRANSCODING = 'TRANSCODING';
    case FINISHED = 'FINISHED';
    case FAILED = 'FAILED';
}
