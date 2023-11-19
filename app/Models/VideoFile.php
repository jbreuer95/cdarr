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
                if ($this->container_format === null || ! str($this->container_format)->contains('mp4')) {
                    return false;
                }
                if ($this->codec === null || $this->codec !== 'h264') {
                    return false;
                }
                if ($this->codec_id === null || $this->codec_id  !== 'avc1') {
                    return false;
                }
                if ($this->profile === null || $this->profile  !== 'high') {
                    return false;
                }
                if ($this->level === null || (int) $this->level  > 41) {
                    return false;
                }
                if ($this->pixel_format === null || $this->pixel_format !== 'yuv420p') {
                    return false;
                }
                if (!in_array($this->color_space, ['bt601', 'bt709'])) {
                    return false;
                }
                if (!in_array($this->color_transfer, [null, 'bt601', 'bt709'])) {
                    return false;
                }
                if (!in_array($this->color_primaries, [null, 'bt601', 'bt709'])) {
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
