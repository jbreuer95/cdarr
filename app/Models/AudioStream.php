<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioStream extends Model
{
    use HasFactory;

    public function videofile()
    {
        return $this->belongsTo(VideoFile::class);
    }
}
