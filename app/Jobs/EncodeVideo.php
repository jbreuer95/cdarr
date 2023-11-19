<?php

namespace App\Jobs;

use App\Enums\EventStatus;
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

class EncodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 0;

    protected VideoFile $file;

    protected ?Event $log = null;
    protected $test;

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
        $this->log = new Event();
        $this->log->type = (new \ReflectionClass($this))->getShortName();
        $this->log->status = EventStatus::RUNNING;
        $this->log->video_file_id = $this->file->id;

        try {
            $this->log->info("Encoding file " . pathinfo($this->file->path, PATHINFO_BASENAME));

            $uuid = Str::uuid()->toString();
            $tmp_location = storage_path("tmp/$uuid");
            File::ensureDirectoryExists($tmp_location);
            $this->log->info("Created temporary directory $uuid");

            $this->log->info('Running ffmpeg command');
            $output = $tmp_location . '/' . pathinfo($this->file->path, PATHINFO_FILENAME) . '.mp4';
            $command = $this->buildCommand($output);
            $this->log->info((new SymfonyProcess($command))->getCommandLine());
            $process = Process::forever()->start($command, function (string $type, string $output) {
                preg_match('/^out_time_us=(\d+)$/m', $output, $matches);
                if (count($matches) === 2) {
                    $percentage = round(($matches[1] / 1000) / $this->file->duration * 100, 2);
                    $this->log->info("Progress {$percentage}%");
                }
            });
            $result = $process->wait();
            if (! $result->successful()) {
                $this->log->status = EventStatus::ERRORED;
                $this->log->info('ffmpeg command failed unexpectedly, exiting');
                $this->log->info($result->errorOutput());
                $this->log->info($result->output());
                return;
            }

            $this->file->analysed = false;
            $this->file->save();

            $this->log->status = EventStatus::FINISHED;
            $this->log->info("Finished encoding file {$output}");
        } catch (\Throwable $th) {
            $this->log->status = EventStatus::ERRORED;
            $this->log->info('Job failed with the following error:');
            $this->log->info($th->getMessage());
        }
    }

    protected function filterAudioStreams()
    {
        $streams = collect([]);
        foreach ($this->file->audiostreams as $audiostream) {
            if ($audiostream->lang !== 'und' && $existing = $streams->where('lang', $audiostream->lang)->first()) {
                if ($existing->channels <= $audiostream->channels) {
                    $this->log->info("Skipping audiostream {$audiostream->index} because an other stream was found with the same language and less channels");
                    continue;
                } else {
                    $this->log->info("Skipping audiostream {$existing->index} because an other stream was found with the same language and less channels");
                    $streams = $streams->reject(function ($item) use ($existing) {
                        return $item === $existing;
                    });
                }
            }
            $streams->push($audiostream);
        }

        return $streams;
    }

    protected function buildCommand($output) {
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
        $command[] = app()->isProduction() ? 'slow' : 'fast';
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
        $command[] = 'format=pix_fmts=yuv420p';
        // TODO force bt709 color space (removes HDR)
        $command[] = '-movflags';
        $command[] = '+faststart';
        $command[] = '-metadata';
        $command[] = 'encoded_by="cdarr"';
        $command[] = '-loglevel';
        $command[] = 'error';
        $command[] = '-progress';
        $command[] = '-';
        $command[] = '-nostats';

        $command[] = $output;

        return $command;
    }
}