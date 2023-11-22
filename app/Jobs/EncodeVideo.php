<?php

namespace App\Jobs;

use App\Enums\EncodeStatus;
use App\Enums\EventStatus;
use App\Models\Encode;
use App\Models\Event;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;
use Illuminate\Support\Str;
use Throwable;

class EncodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 0;

    protected Encode $encode;
    protected VideoFile $file;

    protected ?Event $event = null;

    /**
     * Create a new job instance.
     */
    public function __construct(Encode $encode)
    {
        $this->onQueue('encoding');

        $this->encode = $encode;
        $this->file = $this->encode->videofile;
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
            $this->event->info("Encoding file " . pathinfo($this->file->path, PATHINFO_BASENAME));

            $tmp_output = $this->getTmpLocation() . '/' . pathinfo($this->file->path, PATHINFO_FILENAME) . '.mp4';
            $final_output = pathinfo($this->file->path, PATHINFO_DIRNAME) . '/' . pathinfo($this->file->path, PATHINFO_FILENAME) . '.mp4';

            $command = $this->buildCommand($tmp_output);
            $command_line = (new SymfonyProcess($command))->getCommandLine();

            $this->event->info('Running ffmpeg command');
            $this->event->info($command_line);

            $this->encode->event_id = $this->event->id;
            $this->encode->status = EncodeStatus::TRANSCODING;
            $this->encode->save();

            $process = Process::forever()->start($command, function (string $type, string $output) {
                preg_match('/^out_time_us=(\d+)$/m', $output, $matches);
                if (count($matches) === 2) {
                    $progress = (int) ceil(($matches[1] / 1000) / $this->file->duration * 10000);
                    $this->encode->progress = $progress;
                    $this->encode->save();

                    $this->event->info("Progress ".($progress / 100)."%");
                }
            });
            $result = $process->wait();

            if (! $result->successful()) {
                $this->event->error($result->errorOutput());
                $this->event->error($result->output());

                throw new \Exception('ffmpeg command failed unexpectedly, exiting');
            }

            $this->encode->status = EncodeStatus::FINISHED;
            $this->encode->save();

            $this->event->status = EventStatus::FINISHED;
            $this->event->info("Finished encoding file");

            // TODO what if file is playing/locked?
            File::delete($this->file->path);
            File::move($tmp_output, $final_output);

            $this->file->analysed = false;
            $this->file->save();
            AnalyzeFile::dispatch($this->file);

            // TODO trigger rescan radarr/sonarr
            // TODO trigger rename radarr/sonarr
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
        $this->encode->status = EncodeStatus::FAILED;
        $this->encode->save();

        $event = $this->event ?? $this->encode->event;
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
            $this->file->path
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

        $bitrate = 5000;
        $command[] = '-c:v';
        $command[] = 'libx264';
        $command[] = '-f';
        $command[] = 'mp4';
        $command[] = '-preset';
        $command[] = 'faster';
        $command[] = '-profile:v';
        $command[] = 'high';
        // TODO downscale to 1080p $command[] = '-vf'; $command[] = "scale=w={$width}:h={$height}";
        $command[] = '-crf';
        $command[] = '23';
        $command[] = '-maxrate';
        $command[] = "{$bitrate}k";
        $command[] = '-bufsize';
        $command[] = ($bitrate * 2).'k';
        $command[] = '-vf';
        if (false) { // TODO if HDR
            $command[] = 'zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0:peak=100,zscale=t=bt709:m=bt709,format=yuv420p,format=pix_fmts=yuv420p';
        } else {
            $command[] = 'format=pix_fmts=yuv420p';
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
