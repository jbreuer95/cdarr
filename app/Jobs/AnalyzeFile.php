<?php

namespace App\Jobs;

use App\Enums\EncodeStatus;
use App\Enums\VideoRange;
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

    protected Event $event;

    protected VideoFile $file;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, VideoFile $file)
    {
        $this->onQueue('encoding');

        $this->event = $event;
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->event->info('Started analyzing file '.$this->file->path);
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

            $videostream = $this->getPrimaryVideoStream($analysis->streams);
            $this->event->info("Determined that stream with index {$videostream->index} is the primary video stream, skipping other video streams");

            $this->file->audiostreams()->delete();
            $this->file->index = $videostream->index;
            $this->file->nb_streams = $analysis->format->nb_streams ?? null;
            $this->file->container_format = $analysis->format->format_name ?? null;
            $this->file->width = $videostream->width ?? null;
            $this->file->height = $videostream->height ?? null;
            $this->file->codec = $videostream->codec_name ?? null;
            $this->file->codec_id = $videostream->codec_tag_string ?? null;
            $this->file->profile = $videostream->profile ?? null;
            $this->file->level = $videostream->level ?? null;
            $this->file->video_range = $this->getVideoRange($videostream);
            $this->file->pixel_format = $videostream->pix_fmt ?? null;
            $this->file->color_range = $videostream->color_range ?? null;
            $this->file->color_space = $videostream->color_space ?? null;
            $this->file->color_transfer = $videostream->color_transfer ?? null;
            $this->file->color_primaries = $videostream->color_primaries ?? null;
            $this->file->chroma_location = $videostream->chroma_location ?? null;
            $this->file->interlaced = $this->detectInterlaced($videostream);
            $this->file->anamorphic  = $this->getAnamorphic($videostream);
            $this->file->bit_depth = $this->getBitDepth($videostream);
            $this->file->frame_rate = $videostream->avg_frame_rate ?? null;
            $this->file->bit_rate = $this->getBestVideoBitRate($videostream, $analysis->format);
            $this->file->duration = $this->getBestRuntime($videostream->duration ?? null, $analysis->format->duration ?? null);
            $this->file->faststart = $this->detectFastStart();
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
            $this->event->info("Video stream color range: {$this->file->color_range}");
            $this->event->info("Video stream color space: {$this->file->color_space}");
            $this->event->info("Video stream color transfer: {$this->file->color_transfer}");
            $this->event->info("Video stream color primaries: {$this->file->color_primaries}");
            $this->event->info("Video stream chroma location: {$this->file->chroma_location}");
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
            $this->event->info('File is '.($this->file->encoded ? '' : 'NOT ').'encoded by cdarr already');
            $this->event->info('File is '.($this->file->playable ? '' : 'NOT ').'direct playable already');
            if ($this->file->encoded === false && $this->file->playable === false) {
                $this->event->info('Dispatching EncodeVideo job');

                $event = new Event();
                $event->type = (new \ReflectionClass(EncodeVideo::class))->getShortName();
                $event->video_file_id = $this->file->id;
                $event->info('Queued encoding file '.pathinfo($this->file->path, PATHINFO_BASENAME));

                $encode = new Encode();
                $encode->status = EncodeStatus::WAITING;
                $encode->video_file_id = $this->file->id;
                $encode->event_id = $event->id;
                $encode->save();

                EncodeVideo::dispatch($event, $encode);
            }

            $this->event->info('Finished analyzing file '.$this->file->path);
        } catch (Throwable $th) {
            $this->failed($th);
        }
    }

    public function failed(Throwable $th): void
    {
        $this->event->error('Job failed with the following error:');
        $this->event->error($th->getMessage());
    }

    public function uniqueId()
    {
        return $this->file->id;
    }

    protected function getBestRuntime($video, $format)
    {
        if (! $video || $video === 0) {
            return (int) round($format * 1000);
        }

        return (int) round($video * 1000);
    }

    protected function getBestVideoBitRate($video, $format)
    {
        if (! empty($video->bit_rate) && $video->bit_rate !== 0) {
            return (int) $video->bit_rate;
        }

        if (! empty($video->tags->BPS) && $video->tags->BPS !== 0) {
            return (int) $video->tags->BPS;
        }

        if (! empty($format->bit_rate) && $format->bit_rate !== 0) {
            return (int) $format->bit_rate;
        }

        return 0;
    }

    protected function getBestAudioBitRate($audio)
    {
        if (! empty($audio->bit_rate) && $audio->bit_rate !== 0) {
            return (int) $audio->bit_rate;
        }

        if (! empty($audio->tags->BPS) && $audio->tags->BPS !== 0) {
            return (int) $audio->tags->BPS;
        }

        return 0;
    }

    protected function getPrimaryVideoStream(array $streams)
    {
        $total = count(array_filter($streams, function ($stream) {
            return $stream->codec_type === 'video';
        }));
        $first = Arr::first($streams, function ($stream) {
            return $stream->codec_type === 'video';
        });

        if ($total <= 1) {
            return $first;
        }

        $firstNonMotion = Arr::first($streams, function ($stream) {
            return $stream->codec_type === 'video' && ! in_array($stream->codec_name, ['mjpeg', 'png']);
        });

        return $firstNonMotion ?? $first;
    }

    protected function getVideoRange($videostream)
    {
        if (!empty($videostream->color_primaries) && in_array(str($videostream->color_primaries)->lower(), ['bt2020'])) {
            return VideoRange::HDR;
        }
        if (!empty($videostream->color_transfer) && in_array(str($videostream->color_transfer)->lower(), ['bt2020-10', 'arib-std-b67', 'smpte2084'])) {
            return VideoRange::HDR;
        }

        return VideoRange::SDR;
    }

    protected function getAnamorphic($videostream)
    {
        $width = $videostream->width ?? null;
        $height = $videostream->height ?? null;
        $sar = $videostream->sample_aspect_ratio ?? null;
        $dar = $videostream->display_aspect_ratio ?? null;

        if (!$sar || !$dar || !$width || !$height) {
            // Not enough info to determine, fallback to false because anamorphic is not common
            return false;
        }
        if ($sar === '1:1') {
            return false;
        }
        if ($sar !== '0:1') {
            return true;
        }
        if ($dar === '0:1') {
            return false;
        }
        if ($dar != "{$width}:{$height}") {
            return true;
        }

        return false;
    }

    protected function getBitDepth($videostream)
    {
        if (!empty($videostream->bits_per_sample)) {
            return (int) $videostream->bits_per_sample;
        }
        if (!empty($videostream->bits_per_raw_sample)) {
            return (int) $videostream->bits_per_raw_sample;
        }

        if (!empty($videostream->pix_fmt) && in_array(str($videostream->pix_fmt)->lower(), ['yuv420p', 'yuv444p'])) {
            return 8;
        }

        if (!empty($videostream->pix_fmt) && in_array(str($videostream->pix_fmt)->lower(), ['yuv420p10le', 'yuv444p10le'])) {
            return 10;
        }

        if (!empty($videostream->pix_fmt) && in_array(str($videostream->pix_fmt)->lower(), ['yuv420p12le', 'yuv444p12le'])) {
            return 10;
        }

        return 0;
    }

    protected function detectInterlaced($videostream)
    {
        if (!empty($videostream->field_order) && $videostream->field_order !== 'progressive') {
            return true;
        }

        $this->event->info('Running ffprobe command to determine interlaced video');
        $command = [
            'ffmpeg',
            '-filter:v',
            'idet',
            '-frames:v',
            '100',
            '-an',
            '-f',
            'rawvideo',
            '-y',
            '/dev/null',
            '-i',
            $this->file->path,
        ];
        $this->event->info((new SymfonyProcess($command))->getCommandLine());
        $result = Process::run($command);
        if (! $result->successful()) {
            $this->event->error($result->errorOutput());
            $this->event->error($result->output());

            throw new \Exception('ffmpeg command failed unexpectedly, exiting');
        }

        $output = $result->output() . $result->errorOutput();
        preg_match_all('/TFF:\s*(\d*)/m', $output, $matches, PREG_SET_ORDER, 0);
        if (count($matches) !== 2) {
            throw new \Exception('ffmpeg cannot determine interlacing from output');
        }

        foreach ($matches as $match) {
            if ((int) $match[1] !== 0) {
                return true;
            }
        }

        return false;
    }

    protected function detectFastStart()
    {
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
        $this->event->info('Faststart '.($faststart ? '' : 'NOT ').'detected');

        return $faststart;
    }
}
