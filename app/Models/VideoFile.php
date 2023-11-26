<?php

namespace App\Models;

use App\Enums\VideoRange;
use App\Enums\VideoStatus;
use App\Jobs\AnalyzeFile;
use App\Jobs\EncodeVideo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VideoFile extends Model
{
    use HasFactory;

    protected $casts = [
        'video_range' => VideoRange::class,
        'analysed' => 'boolean',
        'encoded' => 'boolean',
        'interlaced' => 'boolean',
        'anamorphic' => 'boolean',
        'faststart' => 'boolean',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }

    public function audiostreams()
    {
        return $this->hasMany(AudioStream::class);
    }

    public function hasHigherFrameRate(int $rate)
    {
        if ($this->frame_rate === null) {
            return true;
        }

        $parts = explode('/', $this->frame_rate);
        $avg_rate = round($parts[0] / $parts[1], 3);

        return $avg_rate > $rate;
    }

    public function isColorSpaceBT709()
    {
        if ($this->pixel_format === null || ! str($this->pixel_format)->lower()->exactly('yuv420p')) {
            return false;
        }
        if ($this->color_range === null || ! str($this->color_range)->lower()->exactly('tv')) {
            return false;
        }
        if ($this->color_space === null || ! str($this->color_space)->lower()->exactly('bt709')) {
            return false;
        }
        if ($this->color_transfer === null || ! str($this->color_transfer)->lower()->exactly('bt709')) {
            return false;
        }
        if ($this->color_primaries === null || ! str($this->color_primaries)->lower()->exactly('bt709')) {
            return false;
        }
        if ($this->chroma_location === null || ! str($this->chroma_location)->lower()->exactly('left')) {
            return false;
        }

        return true;
    }

    protected function playable(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->analysed === false) {
                    return false;
                }
                if ($this->path === null || ! str($this->path)->lower()->endsWith('.mp4')) {
                    return false;
                }
                if ($this->index === null || $this->index !== 0) {
                    return false;
                }
                if ($this->interlaced) {
                    return false;
                }
                if ($this->anamorphic) {
                    return false;
                }
                if ($this->video_range !== VideoRange::SDR) {
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
                if ($this->profile === null || ! in_array(str($this->profile)->lower(), ['baseline', 'main', 'high'])) {
                    return false;
                }
                if ($this->level === null || (int) $this->level > 41) {
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
                if (! $this->isColorSpaceBT709()) {
                    return false;
                }
                if ($this->hasHigherFrameRate(30)) {
                    return false;
                }

                foreach ($this->audiostreams as $audiostream) {
                    if (! $audiostream->playable) {
                        return false;
                    }
                }

                return true;
            },
        );
    }

    protected function status(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->playable && ! $this->encoded) {
                    return VideoStatus::PLAYABLE_NOT_ENCODED;
                }
                if ($this->playable && $this->encoded) {
                    return VideoStatus::PLAYABLE_ENCODED;
                }
                if (! $this->playable && $this->encoded) {
                    return VideoStatus::NOT_PLAYABLE_ENCODED;
                }
                if ($this->queued(AnalyzeFile::class)) {
                    return VideoStatus::QUEUED_ANALYSING;
                }
                if ($this->queued(EncodeVideo::class)) {
                    return VideoStatus::QUEUED_ENCODING;
                }
                if (! $this->analysed) {
                    return VideoStatus::NOT_ANALYSED;
                }
                if (! $this->playable && ! $this->encoded) {
                    return VideoStatus::NOT_PLAYABLE_NOT_ENCODED;
                }

                return 'Unknown';
            },
        );
    }

    protected function queued($job): bool
    {
        $jobs = DB::table('jobs')->where('payload', 'like', "%".json_encode($job)."%")->get();
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $command = unserialize($payload['data']['command']);

            if ($command->uniqueId() === $this->id) {
                return true;
            }
        }

        return false;
    }
}
