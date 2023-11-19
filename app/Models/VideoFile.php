<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoFile extends Model
{
    use HasFactory;

    protected $casts = [
        'analysed' => 'boolean',
        'encoded' => 'boolean',
        'faststart' => 'boolean',
    ];
    protected $appends = ['compliant'];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function audiostreams()
    {
        return $this->hasMany(AudioStream::class);
    }

    protected function compliant(): Attribute
    {
        return new Attribute(
            get: function ()  {
                if ($this->analysed === false) {
                    return false;
                }
                if ($this->path === null || ! str($this->path)->endsWith('.mp4')) {
                    return false;
                }
                if ($this->index === null || $this->index !== 0) {
                    return false;
                }
                if ($this->container_format === null || $this->container_format !== 'mp4') {
                    return false;
                }
                if ($this->codec === null || $this->codec !== 'h264') {
                    return false;
                }
                // if ($this->codec_id === null || $this->codec_id  !== '?') {
                //     return false;
                // }
                // if ($this->profile === null || $this->profile  !== '?') {
                //     return false;
                // }
                // if ($this->level === null || $this->level  !== '?') {
                //     return false;
                // }
                if ($this->pixel_format === null || $this->pixel_format !== 'yuv420p') {
                    return false;
                }
                // if ($this->color_space !== null && $this->color_space !== 'yuv420p') {
                //     return false;
                // }
                // if ($this->color_primaries !== null && $this->color_primaries !== 'yuv420p') {
                //     return false;
                // }
                if ($this->faststart === null || $this->faststart !== true) {
                    return false;
                }
                if ($this->width === null || $this->width > 1920) {
                    return false;
                }
                if ($this->height === null || $this->height > 1080) {
                    return false;
                }

                foreach ($this->audiostreams as $stream) {
                    if ($stream->codec === null || $stream->codec !== 'aac') {
                        return false;
                    }
                    // if ($stream->codec_id === null || $stream->codec_id !== '?') {
                    //     return false;
                    // }
                    // if ($stream->profile === null || $stream->profile !== '?') {
                    //     return false;
                    // }
                    if ($stream->channels === null || $stream->channels > 2) {
                        return false;
                    }
                    if ($stream->sample_rate === null || $stream->sample_rate !== 48000) {
                        return false;
                    }
                    if ($stream->bit_rate === null || $stream->bit_rate > 160000) {
                        return false;
                    }
                }

                return true;
            },
        );
    }
}
