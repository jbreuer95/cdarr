<?php

namespace App\Jobs;

use App\Enums\EventStatus;
use App\Models\JobLog;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class EncodeVideo implements ShouldQueue
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
        $log->status = EventStatus::RUNNING;
        $log->video_file_id = $this->file->id;

        try {
            $log->info("Encoding file " . pathinfo($this->file->path, PATHINFO_BASENAME));

            $uuid = Str::uuid()->toString();
            $tmp_location = storage_path("tmp/$uuid");
            File::ensureDirectoryExists($tmp_location);

            $log->info("Created temporary directory $uuid");

            $output = $tmp_location . '/' . pathinfo($this->file->path, PATHINFO_FILENAME) . '.mp4';
            $command = [
                'ffmpeg',
                '-i',
                "'{$this->file->path}'",
                '-c',
                'copy',
                '-movflags',
                'use_metadata_tags',
                '-map_metadata',
                '0',
                '-metadata',
                'cdarr_encoded_time="'.(now()->toISOString()).'"',
                '-loglevel',
                'error',
                '-progress',
                '-',
                '-nostats',
                "'{$output}'",
            ];

            $log->info('Running ffmpeg command');
            $log->info(implode(' ', $command));
            $process = Process::start(implode(' ', $command), function (string $type, string $output) use ($log) {
                preg_match('/^out_time_us=(\d+)$/m', $output, $matches);
                if (count($matches) === 2) {
                    $percentage = round(($matches[1] / 1000) / $this->file->duration * 100, 1);
                    $log->info("Progress {$percentage}%");
                } else {
                    $log->info($output);
                }
            });
            $result = $process->wait();
            if (! $result->successful()) {
                $log->status = EventStatus::ERRORED;
                $log->info('ffmpeg command failed unexpectedly, exiting');
                return;
            }

            $log->status = EventStatus::FINISHED;
            $log->info("Finished encoding file {$output}");

            $file = new VideoFile();
            $file->path = $output;
            $file->save();
        } catch (\Throwable $th) {
            $log->status = EventStatus::ERRORED;
            $log->info('Job failed with the following error:');
            $log->info($th->getMessage());
        }
    }
}
