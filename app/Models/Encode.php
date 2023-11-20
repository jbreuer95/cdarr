<?php

namespace App\Models;

use App\Enums\EncodeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encode extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => EncodeStatus::class,
        'progress' => 'integer'
    ];

    public function videofile()
    {
        return $this->belongsTo(VideoFile::class, 'video_file_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
