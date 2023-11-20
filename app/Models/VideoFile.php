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
                if ($this->path === null || ! str($this->path)->lower()->endsWith('.mp4')) {
                    return false;
                }
                if ($this->index === null || $this->index !== 0) {
                    return false;
                }
                if ($this->container_format === null || ! str($this->container_format)->lower()->contains('mp4')) {
                    return false;
                }
                if ($this->codec === null || ! str($this->codec)->lower()->exactly('h264')) {
                    return false;
                }
                if ($this->codec_id === null || ! str($this->codec_id)->lower()->exactly('avc1')) {
                    return false;
                }
                if ($this->profile === null || ! str($this->profile)->lower()->exactly('high')) {
                    return false;
                }
                if ($this->level === null || (int) $this->level  > 41) {
                    return false;
                }
                if ($this->pixel_format === null || ! str($this->pixel_format)->lower()->exactly('yuv420p')) {
                    return false;
                }
                if ($this->color_space === null || ! in_array(str($this->color_space)->lower(), ['bt601', 'bt709'])) {
                    return false;
                }
                if ($this->color_transfer !== null && !in_array(str($this->color_transfer)->lower(), ['bt601', 'bt709'])) {
                    return false;
                }
                if ($this->color_primaries !== null && !in_array(str($this->color_primaries)->lower(), ['bt601', 'bt709'])) {
                    return false;
                }
                if ($this->faststart === null || $this->faststart !== true) {
                    return false;
                }
                if ($this->width === null || $this->width > 1920) {
                    return false;
                }
                if ($this->height === null || $this->height > 1080) {
                    return false;
                }

                foreach ($this->audiostreams as $audiostream) {
                    if (! $audiostream->compliant) {
                        return false;
                    }
                }

                return true;
            },
        );
    }
}
