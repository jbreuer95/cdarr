<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Event extends Model
{
    use HasFactory;

    public function videofile()
    {
        return $this->belongsTo(VideoFile::class, 'video_file_id');
    }

    public function info($line)
    {
        Log::info($line);
        $this->payload .= "$line\n";
        $this->save();
    }

    public function warning($line)
    {
        Log::warning($line);
        $this->payload .= "$line\n";
        $this->save();
    }

    public function error($line)
    {
        Log::error($line);
        $this->payload .= "$line\n";
        $this->save();
    }

    public function toHtml()
    {
        return nl2br(str_replace(' ', '&nbsp;', $this->payload));
    }
}
