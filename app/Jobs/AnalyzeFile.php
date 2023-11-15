<?php

namespace App\Jobs;

use App\Models\AudioStream;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        $info = json_decode($info->output());
        $trace = $trace->output();

        foreach ($info->streams as $stream) {
            if ($stream->codec_type !== 'video') {
                continue;
            }

            $faststart = false;
            if (($moov_pos = strpos($trace, 'moov')) && ($mdat_pos = strpos($trace, 'mdat'))) {
                $faststart = $moov_pos < $mdat_pos;
            }

            $this->file->width = $stream->width ?? null;
            $this->file->height = $stream->height ?? null;
            $this->file->codec = $stream->codec_name ?? null;
            $this->file->profile = $stream->profile ?? null;
            $this->file->level = $stream->level ?? null;
            $this->file->pixel_format = $stream->pix_fmt ?? null;
            $this->file->frame_rate = $stream->avg_frame_rate ?? null;
            $this->file->bit_rate = $stream->bit_rate ?? $info->format->bit_rate ?? null;
            $this->file->duration = (int) round(($stream->duration ?? $info->format->duration ?? 0) * 1000) ?: null;
            $this->file->faststart = $faststart;
            $this->file->save();

            break;
        }

        foreach ($info->streams as $stream) {
            if ($stream->codec_type !== 'audio') {
                continue;
            }

            $audiostream = new AudioStream();
            $audiostream->codec = $stream->codec_name ?? null;
            $audiostream->profile = $stream->profile ?? null;
            $audiostream->lang = $stream->tags->language ?? 'und';
            $audiostream->channels = $stream->channels ?? null;
            $audiostream->sample_rate = $stream->sample_rate ?? null;
            $audiostream->bit_rate = $stream->bit_rate ?? null;
            $audiostream->video_file_id = $this->file->id;
            $audiostream->save();
        }
    }
}
