<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AudioStream extends Model
{
    use HasFactory;

    public function videofile()
    {
        return $this->belongsTo(VideoFile::class, 'video_file_id');
    }

    protected $appends = ['compliant'];

    protected function compliant(): Attribute
    {
        return new Attribute(
            get: function ()  {
                if ($this->codec === null || $this->codec !== 'aac') {
                    return false;
                }
                if ($this->codec_id === null || $this->codec_id !== 'mp4a') {
                    return false;
                }
                if ($this->profile === null || $this->profile !== 'LC') {
                    return false;
                }
                if ($this->channels === null || $this->channels > 2) {
                    return false;
                }
                if ($this->sample_rate === null || $this->sample_rate !== 48000) {
                    return false;
                }
                if ($this->bit_rate === null || $this->bit_rate > 160000) {
                    return false;
                }

                return true;
            },
        );
    }
}
