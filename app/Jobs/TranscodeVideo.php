<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class TranscodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 0;

    protected $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function failed(Throwable $exception)
    {
        Log::info($this->path);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $info = pathinfo($this->path);

        $output = $info['dirname'] . '/' . $info['filename'] . '-transcoding' . '.mp4';

        $video_info =  $this->getVideoInfo($this->path);
        $encoder_level = $this->getEncoderLevel($video_info);
        $encoder_bitrate = $this->getEncoderBitrate($video_info);

        $options = [
            'HandBrakeCLI',
            "--input=".$this->path,
            "--output=$output",
            "--format=av_mp4",
            "--optimize",
            "--markers",
            "--crop=0:0:0:0",
            "--auto-anamorphic",
            "--quality=1",
            "--encoder=x264",
            "--encoder-profile=high",
            "--encoder-level=$encoder_level",
            "--encopts=vbv-maxrate=$encoder_bitrate:vbv-bufsize=".($encoder_bitrate * 2).":crf-max=25:qpmax=34",
            "--all-audio",
            "--aencoder=av_aac",
            "--mixdown=stereo",
        ];

        Log::info("Transcoding: " . $info['basename']);

        $process = new Process($options);
        $process->setTimeout(null);
        $process->setIdleTimeout(60);

        $process->run(function ($type, $buffer) use (&$log) {
            if ($type !== Process::ERR) {
                Log::info($buffer);
                // preg_match('/Encoding: task \d of \d, (\d+.\d+) %/', $buffer, $re);
                // if (isset($re[1])) {}
            } else {
                Log::error($buffer);
            }
        });

        File::delete($this->path);

        $info = pathinfo($output);
        $final = $info['dirname'] . '/' . ltrim(str_replace('-transcoding.mp4', '.mp4', $info['basename']), '.');
        File::move($output, $final);
    }


    protected function getVideoInfo($path)
    {
        $process = new Process([
            'ffprobe',
            '-v',
            'error',
            '-print_format',
            'json',
            '-show_format',
            '-show_streams',
            $path
        ]);
        $process->run();
        $output = json_decode($process->getOutput());
        return $output;
    }

    protected function getEncoderLevel($info)
    {
        if ($info->streams[0]->width > 1920 || $info->streams[0]->height > 1080) {
            return '5.1';
        } else if ($info->streams[0]->width > 1280 || $info->streams[0]->height > 720) {
            return '4.0';
        } else if ($info->streams[0]->width > 720 || $info->streams[0]->height > 576) {
            return '3.1';
        }
        return '3.0';
    }

    protected function getEncoderBitrate($info)
    {
        $bitrate = 1500;
        if ($info->streams[0]->width > 1920 || $info->streams[0]->height > 1080) {
             $bitrate = 12000;
        } else if ($info->streams[0]->width > 1280 || $info->streams[0]->height > 720) {
             $bitrate = 6000;
        } else if ($info->streams[0]->width * $info->streams[0]->height > 720 * 576) {
             $bitrate = 3000;
        }

        $duration = $info->format->duration;
        $size = $info->format->size;
        $media_bitrate = (int) (round((((($size * 8) / $duration) / 1000) / 1000)) * 1000);

        if ($media_bitrate < $bitrate) {
            $min_bitrate = $bitrate / 2;
            if ($media_bitrate < $min_bitrate) {
                $bitrate = $min_bitrate;
            } else {
                $bitrate = $media_bitrate;
            }

        }

        return $bitrate;
    }

}
