<?php

namespace App\Enums;

enum JobLogStatusEnum:string {
    case RUNNING = 'running';
    case FINISHED = 'finished';
    case ERRORED = 'errored';
}
