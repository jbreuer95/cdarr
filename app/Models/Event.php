<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => EventStatus::class
    ];
    public function info($line)
    {
        $this->payload .= "$line\n";
        $this->save();
    }

    public function toHtml()
    {
        return nl2br(str_replace(' ', '&nbsp;', $this->payload));
    }
}
