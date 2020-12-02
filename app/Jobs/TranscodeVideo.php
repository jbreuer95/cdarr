<?php

namespace App\Jobs;

use App\Models\Transcode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class TranscodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 0;

    protected $path;
    protected $transcode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, Transcode $transcode)
    {
        $this->path = $path;
        $this->transcode = $transcode;
    }

    public function failed(Throwable $exception)
    {
        // TODO
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
            "--input=" . $this->path,
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
            "--encopts=vbv-maxrate=$encoder_bitrate:vbv-bufsize=" . ($encoder_bitrate * 2) . ":crf-max=25:qpmax=34",
            "--all-audio",
            "--aencoder=av_aac",
            "--mixdown=stereo",
        ];

        $this->transcode->cmd = implode(' ', $options);
        $this->transcode->status = 'transcoding';
        $this->transcode->save();

        $process = new Process($options);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        $log = $this->transcode->logs()->create();
        $process->run(function ($type, $buffer) use ($log) {
            $log->update(['body' => $log->body . $buffer]);
            preg_match('/Encoding: task \d of \d, (\d+.\d+) %/', $buffer, $re);
            if (isset($re[1])) {
                $this->transcode->progress = intval(round($re[1], 2) * 100);
                $this->transcode->save();
            }
        });
        $this->transcode->touch();

        File::delete($this->path);
        $log = $this->transcode->logs()->create(['body' => 'Deleted: ' . $this->path]);

        $info = pathinfo($output);
        $final = $info['dirname'] . '/' . ltrim(str_replace('-transcoding.mp4', '.mp4', $info['basename']), '.');
        File::move($output, $final);
        $log = $this->transcode->logs()->create(['body' => "Moved $output to $final"]);

        $this->transcode->status = 'finished';
        $this->transcode->save();
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

    protected function findFirstVideoStreamIndex($info)
    {
        foreach ($info->streams as $key => $stream) {
            if ($stream->codec_type === 'video') {
                return $key;
            }
        }

        throw new \Exception('No video stream found');
    }

    protected function getEncoderLevel($info)
    {
        $video_stream_index = $this->findFirstVideoStreamIndex($info);
        $video_stream = $info->streams[$video_stream_index];

        if ($video_stream->width > 1920 || $video_stream->height > 1080) {
            return '5.1';
        } else if ($video_stream->width > 1280 || $video_stream->height > 720) {
            return '4.0';
        } else if ($video_stream->width > 720 || $video_stream->height > 576) {
            return '3.1';
        }
        return '3.0';
    }

    protected function getEncoderBitrate($info)
    {
        $video_stream_index = $this->findFirstVideoStreamIndex($info);
        $video_stream = $info->streams[$video_stream_index];

        $bitrate = 1500;
        if ($video_stream->width > 1920 || $video_stream->height > 1080) {
            $bitrate = 12000;
        } else if ($video_stream->width > 1280 || $video_stream->height > 720) {
            $bitrate = 6000;
        } else if ($video_stream->width * $video_stream->height > 720 * 576) {
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
