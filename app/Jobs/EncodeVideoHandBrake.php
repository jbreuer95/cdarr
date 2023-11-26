<?php

namespace App\Jobs;

use App\Enums\EncodeStatus;
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

class EncodeVideoHandBrake implements ShouldQueue
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

            $tmp_directory = $this->getTmpLocation();
            $tmp_remux_output = $tmp_directory.'/'.pathinfo($this->file->path, PATHINFO_FILENAME).'_remux.'.pathinfo($this->file->path, PATHINFO_EXTENSION);
            $tmp_output = $tmp_directory.'/'.pathinfo($this->file->path, PATHINFO_FILENAME).'.mp4';
            $final_output = pathinfo($this->file->path, PATHINFO_DIRNAME).'/'.pathinfo($this->file->path, PATHINFO_FILENAME).'.mp4';

            $remux_command = $this->buildRemuxCommand($tmp_remux_output);
            $encode_command = $this->buildEncodeCommand($tmp_remux_output, $tmp_output);

            $this->event->info('Running ffmpeg command');
            $this->event->info((new SymfonyProcess($remux_command))->getCommandLine());

            $this->encode->event_id = $this->event->id;
            $this->encode->status = EncodeStatus::REMUXING;
            $this->encode->created_at = Date::now();
            $this->encode->save();

            $process = Process::forever()->start($remux_command, function (string $type, string $output) {
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

            $this->encode->status = EncodeStatus::TRANSCODING;
            $this->encode->progress = 0;
            $this->encode->save();

            $this->event->info('Running handbrake command');
            $this->event->info((new SymfonyProcess($encode_command))->getCommandLine());

            $process = Process::forever()->start($encode_command, function (string $type, string $output) {
                preg_match('/Encoding: task \d of \d, (\d+.\d+) %/', $output, $matches);
                if (isset($matches[1])) {
                    $progress = intval(round($matches[1], 2) * 100);
                    $this->encode->progress = $progress;
                    $this->encode->save();

                    $this->event->info('Progress '.($progress / 100).'%');
                }
            });
            $result = $process->wait();

            if (! $result->successful()) {
                $this->event->error($result->errorOutput());
                $this->event->error($result->output());

                throw new \Exception('handbrake command failed unexpectedly, exiting');
            }

            $this->event->info('Finished encoding file');
            $this->encode->status = EncodeStatus::FINISHED;
            $this->encode->progress = 10000;
            $this->encode->save();

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

    protected function buildRemuxCommand($output)
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

        $command[] = '-c';
        $command[] = 'copy';

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

    protected function buildEncodeCommand($input, $output)
    {
        $bitrate = 5000; // TODO determine based on input resolution

        $command = [
            'HandBrakeCLI',
            "--input={$input}",
            "--output={$output}",
        ];

        $command[] = "--format=av_mp4";

        $command[] = "--encoder=x264";
        $command[] = "--quality=23";
        $command[] = "--encopts=vbv-maxrate=$bitrate:vbv-bufsize=" . ($bitrate * 2);
        $command[] = "--encoder-profile=high";
        $command[] = "--encoder-level=4.1";
        $command[] = "--encoder-preset=faster";

        // $command[] = "--crop-mode=none"; // TODO update cli version sail
        $command[] = "--crop=0:0:0:0";
        $command[] = "--cfr";
        // $command[] = "--deinterlace";
        // $command[] = "--colorspace=bt709";

        $command[] = "--all-audio";
        $command[] = "--aencoder=av_aac";
        $command[] = "--ab=128";
        $command[] = "--arate=48";
        $command[] = "--mixdown=stereo";

        $command[] = "--optimize";
        $command[] = "--no-markers";
        $command[] = "--align-av";


        // // $command[] = '-vf'; $command[] = "scale=w={$width}:h={$height}";
        // $command[] = '-vf';
        // // $command[] = 'zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0:peak=100,zscale=t=bt709:m=bt709,format=yuv420p,format=pix_fmts=yuv420p';
        // $command[] = 'format=pix_fmts=yuv420p';

        // $command[] = $output;


        return $command;
    }
}
