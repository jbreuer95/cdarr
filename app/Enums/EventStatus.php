<?php

namespace App\Enums;

enum EventStatus:string
{
    case RUNNING = 'RUNNING';
    case FINISHED = 'FINISHED';
    case ERRORED = 'ERRORED';
}
