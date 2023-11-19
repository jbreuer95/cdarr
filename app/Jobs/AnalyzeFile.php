<?php

namespace App\Jobs;

use App\Enums\JobLogStatusEnum;
use App\Models\AudioStream;
use App\Models\JobLog;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
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
        $log = new JobLog();
        $log->type = (new \ReflectionClass($this))->getShortName();
        $log->status = JobLogStatusEnum::RUNNING;
        $log->video_file_id = $this->file->id;

        try {
            $log->info('Starting analyzing file ' . $this->file->path);

            $command = [
                'ffprobe',
                '-v',
                'error',
                '-print_format',
                'json',
                '-show_format',
                '-show_streams',
                "'{$this->file->path}'",
            ];

            $log->info('Running ffprobe command to get stream info');
            $log->info(implode(' ', $command));
            $info = Process::run(implode(' ', $command));
            if ($output = $info->output()) {
                $log->info('Received the following output');
                $log->info(json_encode(json_decode($output), JSON_PRETTY_PRINT));
            } else {
                $log->info('Received NO output');
            }

            $command = [
                'ffprobe',
                '-v',
                'trace',
                '-i',
                "'{$this->file->path}'",
                '2>&1',
                '|',
                'grep',
                '-e',
                "type:\'mdat\'",
                '-e',
                "type:\'moov\'",
            ];

            $log->info('Running ffprobe command to determine faststart');
            $log->info(implode(' ', $command));
            $trace = Process::run(implode(' ', $command));
            if ($output = $trace->output()) {
                $log->info('Received output');
                $log->info(json_encode(json_decode($output), JSON_PRETTY_PRINT));
            } else {
                $log->info('Received NO output');
            }

            $analysis = json_decode($info->output());
            $videostream = $this->getPrimaryVideoStream($analysis->streams);
            $log->info("Determined that stream with index {$videostream->index} is the primary video stream");

            $faststart = false;
            if (($moov_pos = strpos($trace->output(), 'moov')) && ($mdat_pos = strpos($trace->output(), 'mdat'))) {
                $faststart = $moov_pos < $mdat_pos;
            }


            // TODO: multiview (3D) detection
            // TODO: HDR detection
            $this->file->index = $videostream->index;
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
            $this->file->bit_rate = $this->getBestVideoBitRate($videostream, $analysis->format);
            $this->file->duration = $this->getBestRuntime($videostream->duration ?? null, $analysis->format->duration ?? null);
            $this->file->faststart = $faststart;
            $this->file->save();

            $log->info("Video stream index: {$this->file->index}");
            $log->info("Video stream container_format: {$this->file->container_format}");
            $log->info("Video stream width: {$this->file->width}");
            $log->info("Video stream height: {$this->file->height}");
            $log->info("Video stream codec: {$this->file->codec}");
            $log->info("Video stream codec_id: {$this->file->codec_id}");
            $log->info("Video stream profile: {$this->file->profile}");
            $log->info("Video stream level: {$this->file->level}");
            $log->info("Video stream pixel_format: {$this->file->pixel_format}");
            $log->info("Video stream color_space: {$this->file->color_space}");
            $log->info("Video stream color_transfer: {$this->file->color_transfer}");
            $log->info("Video stream color_primaries: {$this->file->color_primaries}");
            $log->info("Video stream frame_rate: {$this->file->frame_rate}");
            $log->info("Video stream bit_rate: {$this->file->bit_rate}");
            $log->info("Video stream duration: {$this->file->duration}");
            $log->info("Video stream faststart: " . ($this->file->faststart ? 'Yes' : 'No'));

            foreach ($analysis->streams as $stream) {
                if ($stream->codec_type !== 'audio') {
                    continue;
                }

                $audiostream = new AudioStream();
                $audiostream->index = $stream->index;
                $audiostream->lang = $stream->tags->language ?? 'und';
                $audiostream->codec = $stream->codec_name ?? null;
                $audiostream->codec_id = $stream->codec_tag_string ?? null;
                $audiostream->profile = $stream->profile ?? null;
                $audiostream->channels = $stream->channels ?? null;
                $audiostream->sample_rate = $stream->sample_rate ?? null;
                $audiostream->bit_rate = $this->getBestAudioBitRate($stream);
                $audiostream->video_file_id = $this->file->id;
                $audiostream->save();

                $log->info("Audio stream {$audiostream->index} index: {$audiostream->index}");
                $log->info("Audio stream {$audiostream->index} lang: {$audiostream->lang}");
                $log->info("Audio stream {$audiostream->index} codec: {$audiostream->codec}");
                $log->info("Audio stream {$audiostream->index} codec_id: {$audiostream->codec_id}");
                $log->info("Audio stream {$audiostream->index} profile: {$audiostream->profile}");
                $log->info("Audio stream {$audiostream->index} channels: {$audiostream->channels}");
                $log->info("Audio stream {$audiostream->index} sample_rate: {$audiostream->sample_rate}");
                $log->info("Audio stream {$audiostream->index} bit_rate: {$audiostream->bit_rate}");
            }

            $log->status = JobLogStatusEnum::FINISHED;
            $log->info('Finished analyzing file ' . $this->file->path);
        } catch (\Throwable $th) {
            $log->status = JobLogStatusEnum::ERRORED;
            $log->info('Job failed with the following error:');
            $log->info($th->getMessage());
        }
    }

    protected function getBestRuntime($video, $format)
    {
        if (!$video || $video === 0) {
            return (int) round($format * 1000);
        }

        return (int) round($video * 1000);
    }

    protected function getBestVideoBitRate($video, $format)
    {
        if (!empty($video->bit_rate) && $video->bit_rate !== 0) {
            return (int) $video->bit_rate;
        }

        if (!empty($video->tags->BPS) && $video->tags->BPS !== 0) {
            return (int) $video->tags->BPS;
        }

        if (!empty($format->bit_rate) && $format->bit_rate !== 0) {
            return (int) $format->bit_rate;
        }

        return 0;
    }

    protected function getBestAudioBitRate($audio)
    {
        if (!empty($audio->bit_rate) && $audio->bit_rate !== 0) {
            return (int) $audio->bit_rate;
        }

        if (!empty($audio->tags->BPS) && $audio->tags->BPS !== 0) {
            return (int) $audio->tags->BPS;
        }

        return 0;
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
