<?php

namespace App\Jobs;

use App\Enums\EncodeStatus;
use App\Enums\EventStatus;
use App\Models\AudioStream;
use App\Models\Encode;
use App\Models\Event;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;
use Throwable;

class AnalyzeFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 0;

    protected VideoFile $file;

    protected ?Event $event = null;

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
        $this->event = new Event();
        $this->event->type = (new \ReflectionClass($this))->getShortName();
        $this->event->status = EventStatus::RUNNING;
        $this->event->video_file_id = $this->file->id;

        try {
            $this->event->info('Analyzing file ' . $this->file->path);
            $this->event->info('Running ffprobe command to get stream info');
            $command = [
                'ffprobe',
                '-v',
                'error',
                '-print_format',
                'json',
                '-show_format',
                '-show_streams',
                $this->file->path,
            ];
            $this->event->info((new SymfonyProcess($command))->getCommandLine());
            $result = Process::run($command);
            if (! $result->successful()) {
                $this->event->error($result->errorOutput());
                $this->event->error($result->output());

                throw new \Exception('ffprobe command failed unexpectedly, exiting');
            }

            $analysis = json_decode($result->output());
            if (! $analysis) {
                throw new \Exception('ffprobe gave no readable data, exiting');
            }

            $this->event->info(json_encode($analysis, JSON_PRETTY_PRINT));

            $this->event->info('Running ffprobe command to determine faststart');
            $command = [
                'ffprobe',
                '-v',
                'trace',
                '-i',
                $this->file->path,
            ];
            $this->event->info((new SymfonyProcess($command))->getCommandLine());
            $result = Process::run($command);
            if (! $result->successful()) {
                $this->event->error($result->errorOutput());
                $this->event->error($result->output());

                throw new \Exception('ffprobe command failed unexpectedly, exiting');
            }

            $faststart = false;
            if (($moov_pos = strpos($result->errorOutput(), 'moov')) && ($mdat_pos = strpos($result->errorOutput(), 'mdat'))) {
                $faststart = $moov_pos < $mdat_pos;
            }
            $this->event->info('Faststart '. ($faststart ? '' : 'NOT ') . 'detected');

            $videostream = $this->getPrimaryVideoStream($analysis->streams);
            $this->event->info("Determined that stream with index {$videostream->index} is the primary video stream, skipping other video streams");

            // TODO: multiview (3D) detection
            // TODO: HDR detection
            $this->file->audiostreams()->delete();
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
            $this->file->encoded = str($analysis->format->tags->comment ?? '')->contains('cdarr');
            $this->file->analysed = true;
            $this->file->save();

            $this->event->info("Video stream index: {$this->file->index}");
            $this->event->info("Video stream container_format: {$this->file->container_format}");
            $this->event->info("Video stream width: {$this->file->width}");
            $this->event->info("Video stream height: {$this->file->height}");
            $this->event->info("Video stream codec: {$this->file->codec}");
            $this->event->info("Video stream codec_id: {$this->file->codec_id}");
            $this->event->info("Video stream profile: {$this->file->profile}");
            $this->event->info("Video stream level: {$this->file->level}");
            $this->event->info("Video stream pixel_format: {$this->file->pixel_format}");
            $this->event->info("Video stream color_space: {$this->file->color_space}");
            $this->event->info("Video stream color_transfer: {$this->file->color_transfer}");
            $this->event->info("Video stream color_primaries: {$this->file->color_primaries}");
            $this->event->info("Video stream frame_rate: {$this->file->frame_rate}");
            $this->event->info("Video stream bit_rate: {$this->file->bit_rate}");
            $this->event->info("Video stream duration: {$this->file->duration}");

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

                $this->event->info("Audio stream {$audiostream->index} index: {$audiostream->index}");
                $this->event->info("Audio stream {$audiostream->index} lang: {$audiostream->lang}");
                $this->event->info("Audio stream {$audiostream->index} codec: {$audiostream->codec}");
                $this->event->info("Audio stream {$audiostream->index} codec_id: {$audiostream->codec_id}");
                $this->event->info("Audio stream {$audiostream->index} profile: {$audiostream->profile}");
                $this->event->info("Audio stream {$audiostream->index} channels: {$audiostream->channels}");
                $this->event->info("Audio stream {$audiostream->index} sample_rate: {$audiostream->sample_rate}");
                $this->event->info("Audio stream {$audiostream->index} bit_rate: {$audiostream->bit_rate}");
            }



            $this->file->refresh();
            $this->event->info('File is '. ($this->file->encoded ? '' : 'NOT ') . 'encoded by cdarr already');
            $this->event->info('File is '. ($this->file->compliant ? '' : 'NOT ') . 'compliant for direct play already');
            if ($this->file->encoded === false && $this->file->compliant === false) {
                $this->event->info('Dispatching EncodeVideo job');

                $encode = new Encode();
                $encode->status = EncodeStatus::WAITING;
                $encode->video_file_id = $this->file->id;
                $encode->save();

                EncodeVideo::dispatch($encode);
            }

            $this->event->status = EventStatus::FINISHED;
            $this->event->info('Finished analyzing file ' . $this->file->path);
        } catch (Throwable $th) {
            $this->logFailure($th);
        }
    }

    public function failed(Throwable $th): void
    {
        $this->logFailure($th);
    }

    protected function logFailure(Throwable $th)
    {
        $event = $this->event;
        if (!$event) {
            $event = Event::where('video_file_id', $this->file->id)
                ->whereNotIn('status', [EventStatus::ERRORED, EventStatus::FINISHED])
                ->where('type', (new \ReflectionClass($this))->getShortName())
                ->orderByDesc('id')
                ->first();
        }
        if ($event) {
            $event->status = EventStatus::ERRORED;
            $event->error('Job failed with the following error:');
            $event->error($th->getMessage());
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
