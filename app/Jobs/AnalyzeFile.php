<?php

namespace App\Jobs;

use App\Models\AudioStream;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class AnalyzeFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 0;


    protected VideoFile $file;

    /**
     * Create a new job instance.
     */
    public function __construct(VideoFile $file)
    {
        $this->onQueue('encoding');

        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Analyzing ' . $this->file->path);
        $info = Process::run([
            'ffprobe',
            '-v',
            'error',
            '-print_format',
            'json',
            '-show_format',
            '-show_streams',
            $this->file->path,
        ]);
        $trace = Process::run("ffprobe -v trace -i '".$this->file->path."' 2>&1 | grep -e type:\'mdat\' -e type:\'moov\'");

        Log::info($info->output());
        Log::info($trace->output());

        $analysis = json_decode($info->output());
        $videostream = $this->getPrimaryVideoStream($analysis->streams);

        $faststart = false;
        if (($moov_pos = strpos($trace->output(), 'moov')) && ($mdat_pos = strpos($trace->output(), 'mdat'))) {
            $faststart = $moov_pos < $mdat_pos;
        }


        // TODO: multiview (3D) detection
        // TODO: HDR detection
        $this->file->container_format = $analysis->format->format_name ?? null;
        $this->file->width = $videostream->width ?? null;
        $this->file->height = $videostream->height ?? null;
        $this->file->codec = $videostream->codec_name ?? null;
        $this->file->codec_id = $videostream->codec_tag_string ?? null;
        $this->file->profile = $videostream->profile ?? null;
        $this->file->level = $videostream->level ?? null;
        $this->file->pixel_format = $videostream->pix_fmt ?? null;
        $this->file->color_space = $videostream->color_space ?? null;
        $this->file->color_transfer = $videostream->color_transfer ?? null;
        $this->file->color_primaries = $videostream->color_primaries ?? null;
        $this->file->frame_rate = $videostream->avg_frame_rate ?? null;
        $this->file->bit_rate = $videostream->bit_rate ?? null;
        $this->file->duration = $this->getBestRuntime($videostream->duration ?? null, $analysis->format->duration ?? null);
        $this->file->faststart = $faststart;
        $this->file->save();

        foreach ($analysis->streams as $stream) {
            if ($stream->codec_type !== 'audio') {
                continue;
            }

            $audiostream = new AudioStream();
            $audiostream->codec = $stream->codec_name ?? null;
            $audiostream->codec_id = $stream->codec_tag_string ?? null;
            $audiostream->profile = $stream->profile ?? null;
            $audiostream->lang = $stream->tags->language ?? 'und';
            $audiostream->channels = $stream->channels ?? null;
            $audiostream->sample_rate = $stream->sample_rate ?? null;
            $audiostream->bit_rate = $stream->bit_rate ?? null;
            $audiostream->video_file_id = $this->file->id;
            $audiostream->save();
        }
    }

    protected function getBestRuntime($video, $general)
    {
        if (!$video || $video === 0) {
            return (int) round($general * 1000);
        }

        return (int) round($video * 1000);
    }

    protected function getPrimaryVideoStream(array $streams)
    {
        $total = count(array_filter($streams, function($stream) {
            return $stream->codec_type === 'video';
        }));
        $first = Arr::first($streams, function ($stream) {
            return $stream->codec_type === 'video';
        });

        if ($total <= 1) {
            return  $first;
        }

        $firstNonMotion = Arr::first($streams, function ($stream) {
            return $stream->codec_type === 'video' && !in_array($stream->codec_name, ['mjpeg', 'png']);
        });

        return $firstNonMotion ?? $first;
    }
}
