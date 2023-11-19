<?php

namespace App\Enums;

enum EventStatus:string {
    case RUNNING = 'running';
    case FINISHED = 'finished';
    case ERRORED = 'errored';
}
