<?php

namespace App\Jobs;

use App\Enums\EncodeStatus;
use App\Enums\VideoRange;
use App\Models\Encode;
use App\Models\Event;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process as SymfonyProcess;
use Throwable;

class EncodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 0;

    protected Event $event;

    protected Encode $encode;

    protected VideoFile $file;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, Encode $encode)
    {
        $this->onQueue('encoding');

        $this->event = $event;
        $this->encode = $encode;
        $this->file = $this->encode->videofile;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->event->info('Started encoding file '.pathinfo($this->file->path, PATHINFO_BASENAME));

            $tmp_output = $this->getTmpLocation().'/'.pathinfo($this->file->path, PATHINFO_FILENAME).'.mp4';
            $final_output = pathinfo($this->file->path, PATHINFO_DIRNAME).'/'.pathinfo($this->file->path, PATHINFO_FILENAME).'.mp4';

            $command = $this->buildCommand($tmp_output);
            $command_line = (new SymfonyProcess($command))->getCommandLine();

            $this->event->info('Running ffmpeg command');
            $this->event->info($command_line);

            $this->encode->event_id = $this->event->id;
            $this->encode->status = EncodeStatus::TRANSCODING;
            $this->encode->created_at = Date::now();
            $this->encode->save();

            $process = Process::forever()->start($command, function (string $type, string $output) {
                preg_match('/^out_time_us=(\d+)$/m', $output, $matches);
                if (count($matches) === 2) {
                    $progress = (int) ceil(($matches[1] / 1000) / $this->file->duration * 10000);
                    $this->encode->progress = $progress;
                    $this->encode->save();

                    $this->event->info('Progress '.($progress / 100).'%');
                }
            });
            $result = $process->wait();

            if (! $result->successful()) {
                $this->event->error($result->errorOutput());
                $this->event->error($result->output());

                throw new \Exception('ffmpeg command failed unexpectedly, exiting');
            }

            $this->encode->status = EncodeStatus::FINISHED;
            $this->encode->progress = 10000;
            $this->encode->save();

            $this->event->info('Finished encoding file');
            File::delete($this->file->path);
            File::move($tmp_output, $final_output);

            $this->file->path = $final_output;
            $this->file->analysed = false;
            $this->file->save();

            $this->event->info('Dispatching new AnalyzeFile job');

            $event = new Event();
            $event->type = (new \ReflectionClass(AnalyzeFile::class))->getShortName();
            $event->video_file_id = $this->file->id;
            $event->info('Queued analyzing file '.pathinfo($this->file->path, PATHINFO_BASENAME));

            AnalyzeFile::dispatch($event, $this->file);
        } catch (Throwable $th) {
            $this->failed($th);
        }
    }

    public function failed(Throwable $th): void
    {
        $this->encode->status = EncodeStatus::FAILED;
        $this->encode->save();

        $this->event->error('Job failed with the following error:');
        $this->event->error($th->getMessage());
    }

    public function uniqueId()
    {
        return $this->file->id;
    }

    protected function getTmpLocation()
    {
        $uuid = Str::uuid()->toString();
        $tmp_location = storage_path("tmp/$uuid");
        File::ensureDirectoryExists($tmp_location);
        $this->event->info("Created temporary directory $uuid");

        return $tmp_location;
    }

    protected function filterAudioStreams()
    {
        $streams = collect([]);
        foreach ($this->file->audiostreams as $audiostream) {
            if ($audiostream->lang !== 'und' && $existing = $streams->where('lang', $audiostream->lang)->first()) {
                if ($existing->channels <= $audiostream->channels) {
                    $this->event->info("Skipping audiostream {$audiostream->index} because an other stream was found with the same language and less channels");

                    continue;
                } else {
                    $this->event->info("Skipping audiostream {$existing->index} because an other stream was found with the same language and less channels");
                    $streams = $streams->reject(function ($item) use ($existing) {
                        return $item === $existing;
                    });
                }
            }
            $streams->push($audiostream);
        }

        return $streams;
    }

    protected function buildCommand($output)
    {
        $command = [
            'ffmpeg',
            '-i',
            $this->file->path,
        ];

        $command[] = '-map';
        $command[] = "0:{$this->file->index}";

        $audiostreams = $this->filterAudioStreams();
        foreach ($audiostreams as $audiostream) {
            $command[] = '-map';
            $command[] = "0:{$audiostream->index}";
        }

        $command[] = '-sn'; // Disable subtitles tracks from getting mapped
        $command[] = '-dn'; // Disable data tracks from getting mapped
        $command[] = '-map_chapters'; // Disable chapter data track from being created
        $command[] = '-1';

        $command[] = '-c:a';
        $command[] = 'aac';
        $command[] = '-ac';
        $command[] = '2';
        $command[] = '-b:a';
        $command[] = '128k';
        $command[] = '-ar';
        $command[] = '48000';

        $bitrate = 5000;

        $command[] = '-c:v';
        $command[] = 'libx264';
        $command[] = '-f';
        $command[] = 'mp4';
        $command[] = '-preset';
        $command[] = 'faster';
        $command[] = '-profile:v';
        $command[] = 'high';
        // $command[] = '-vf'; $command[] = "scale=w={$width}:h={$height}";
        $command[] = '-crf';
        $command[] = '23';
        $command[] = '-maxrate';
        $command[] = "{$bitrate}k";
        $command[] = '-bufsize';
        $command[] = ($bitrate * 2).'k';

        $command[] = '-vsync';
        $command[] = 'cfr';

        if ($this->file->hasHigherFrameRate(30)) {
            $command[] = '-r';
            $command[] = '30';
        }

        // $command[] = 'zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0:peak=100,zscale=t=bt709:m=bt709,format=yuv420p,format=pix_fmts=yuv420p';

        $video_filters = [];
        $scale_filter = $this->file->video_range === VideoRange::HDR ? 'zscale' : 'scale';

        if ($this->file->interlaced) {
            $video_filters[] = 'yadif';
        }

        if ($this->file->anamorphic) {
            $video_filters[] = "{$scale_filter}=iw*sar:ih";
        }

        if ($this->file->width > 1920 || $this->file->height > 1080) {
            if ($this->file->width > $this->file->height) {
                $video_filters[] = "{$scale_filter}=1920:-2";
            } else {
                $video_filters[] = "{$scale_filter}=-2:1080";
            }
        }

        if ($this->file->video_range === VideoRange::HDR) {
            $video_filters[] = 'zscale=t=linear:npl=100';
            $video_filters[] = 'format=gbrpf32le';
            $video_filters[] = 'zscale=primaries=bt709';
            $video_filters[] = 'tonemap=tonemap=hable:desat=0:peak=100';
            $video_filters[] = 'zscale=transfer=bt709:matrix=bt709:chromal=left:range=tv';
            $video_filters[] = 'format=yuv420p';
        } else if ($this->file->video_range === VideoRange::SDR && ! $this->file->isColorSpaceBT709()) {
            $video_filters[] = 'scale=in_color_matrix=auto:in_range=auto:out_color_matrix=bt709:out_range=tv';
        }

        if ($this->file->anamorphic) {
            $video_filters[] = 'setsar=1';
        }

        if (count($video_filters) > 0) {
            $command[] = '-vf';
            $command[] = implode(',', $video_filters);
        }

        if ($this->file->video_range !== VideoRange::HDR && ! $this->file->isColorSpaceBT709()) {
            $command[] = '-pix_fmt:v';
            $command[] = 'yuv420p';
            $command[] = '-colorspace:v';
            $command[] = 'bt709';
            $command[] = '-color_primaries:v';
            $command[] = 'bt709';
            $command[] = '-color_trc:v';
            $command[] = 'bt709';
            $command[] = '-color_range:v';
            $command[] = 'tv';
            $command[] = '-chroma_sample_location:v';
            $command[] = 'left';
        }

        $command[] = '-movflags';
        $command[] = '+faststart';
        $command[] = '-metadata';
        $command[] = "comment='Encoded by cdarr on ".now()->format('Y-m-d H:i:s')."'";
        $command[] = '-loglevel';
        $command[] = 'error';
        $command[] = '-progress';
        $command[] = '-';
        $command[] = '-nostats';

        $command[] = $output;

        return $command;
    }
}
