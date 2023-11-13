<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class Debug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug command';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $folder = '/data/media/movies';
        $files = File::allFiles($folder);
        $movies = collect([]);
        foreach ($files as $file) {
            $this->line('Analyzing '.Str::replace($folder, '', $file->getPathname()));
            $result = Process::run([
                'ffprobe',
                '-v',
                'error',
                '-print_format',
                'json',
                '-show_format',
                '-show_streams',
                $file->getPathname(),
            ]);

            $info = json_decode($result->output());

            $this->line('Detected <fg=green>'.count($info->streams).'</> streams');

            $video = null;
            $audio = collect([]);
            foreach ($info->streams as $stream) {
                if ($stream->codec_type === 'video' && ! $video) {
                    $avg_frame_rate = explode('/', $stream->avg_frame_rate);
                    $framerate = round($avg_frame_rate[0] / $avg_frame_rate[1], 3);
                    $video = (object) [
                        'index' => $stream->index,
                        'width' => $stream->width,
                        'height' => $stream->height,
                        'aspect_ratio' => $stream->display_aspect_ratio,
                        'codec' => $stream->codec_name,
                        'profile' => $stream->profile,
                        'level' => $stream->level,
                        'color_space' => $stream->pix_fmt,
                        'frame_rate' => $framerate,
                        'bit_rate' => $stream->bit_rate ?? $info->format->bit_rate,
                        'duration' => (int) round($stream->duration ?? $info->format->duration * 1000),
                    ];

                    $this->line("Video stream found with dimensions: <fg=green>{$stream->width}x{$stream->height}</>");
                }
                if ($stream->codec_type === 'audio') {
                    $lang = $stream->tags->language ?? 'und';
                    $channels = $stream->channels;

                    $this->line("Audio stream found with <fg=green>{$channels}</> channels with language <fg=green>{$lang}</>");

                    if ($lang !== 'und' && $existing = $audio->where('lang', $lang)->first()) {
                        if ($existing->channels <= $channels) {
                            continue;
                        } else {
                            $audio = $audio->reject(function ($item) use ($existing) {
                                return $item === $existing;
                            });
                        }
                    }

                    $audio->push((object) [
                        'index' => $stream->index,
                        'lang' => $lang,
                        'channels' => $channels,
                        'sample_rate' => $stream->sample_rate,
                        'bit_rate' => $stream->bit_rate ?? $info->format->bit_rate,
                        'duration' => (int) round($stream->duration ?? $info->format->duration * 1000),
                    ]);
                }
            }

            $movies->push((object) [
                'name' => $file->getFilenameWithoutExtension(),
                'path' => $file->getPathname(),
                'video' => $video,
                'audio' => $audio,
            ]);
        }

        foreach ($movies as $movie) {
            $this->line("Started encoding job for <fg=green>{$movie->name}</>");

            $uuid = Str::uuid()->toString();
            $tmp_location = storage_path("tmp/$uuid");
            File::ensureDirectoryExists($tmp_location);

            $this->line("Created temporary directory <fg=green>$uuid</>");

            // $command = ['ffmpeg', '-i', $movie->path];
        }

        //     foreach ($movie->audio as $stream) {
        //         $file = "{$tmp_location}/{$stream->lang}.m4a";
        //         $this->line("Extracting and encoding <fg=green>{$stream->lang}</> stream to <fg=green>{$stream->lang}.m4a</>");

        //         $bar = $this->output->createProgressBar($stream->duration);
        //         $process = Process::start([
        //             'ffmpeg',
        //             '-i',
        //             $movie->path,
        //             '-map',
        //             "0:{$stream->index}",
        //             '-c:a',
        //             'aac',
        //             '-ac',
        //             '2',
        //             '-b:a',
        //             '128k',
        //             '-loglevel',
        //             'error',
        //             '-progress',
        //             '-',
        //             '-nostats',
        //             $file,
        //         ], function (string $type, string $output) use (&$bar) {
        //             preg_match('/^out_time_us=(\d+)$/m', $output, $matches);
        //             if (count($matches) !== 2) {
        //                 return;
        //             }

        //             $bar->setProgress((int) round($matches[1] / 1000));
        //         });
        //         $result = $process->wait();
        //         $bar->finish();
        //         $this->newLine();
        //     }

        //     $ladder = [
        //         '3840x2160' => 20000,
        //         '1920x1080' => 6000,
        //         '1280x720' => 3000,
        //         '768x432' => 1100,
        //     ];

        //     foreach ($ladder as $size => $bitrate) {
        //         $width = (int) explode('x', $size)[0];
        //         $height = (int) explode('x', $size)[1];

        //         if ($movie->video->width < $width && $movie->video->height < $height) {
        //             $this->warn("Video input size not big enough for ladder size <fg=green>{$size}</>, skipping");

        //             continue;
        //         }

        //         $height = (int) ceil(($movie->video->height / $movie->video->width) * $width);
        //         $size = "{$width}x{$height}";

        //         $file = "{$tmp_location}/{$size}_{$bitrate}.mp4";
        //         $this->line("Encoding {$size} variant with {$bitrate}kbps bitrate to <fg=green>{$size}_{$bitrate}.mp4</>");

        //         $bar = $this->output->createProgressBar($movie->video->duration);
        //         $process = Process::timeout(0)->idleTimeout(0)->start([
        //             'ffmpeg',
        //             '-i',
        //             $movie->path,
        //             '-map',
        //             "0:{$movie->video->index}",
        //             '-profile:v',
        //             'high',
        //             '-vf',
        //             "scale=w={$width}:h={$height}",
        //             '-crf',
        //             '23',
        //             '-maxrate',
        //             "{$bitrate}k",
        //             '-bufsize',
        //             ($bitrate * 2).'k',
        //             '-pix_fmt',
        //             'yuv420p',
        //             '-movflags',
        //             '+faststart',
        //             '-loglevel',
        //             'error',
        //             '-progress',
        //             '-',
        //             '-nostats',
        //             $file,
        //         ], function (string $type, string $output) use (&$bar) {
        //             preg_match('/^out_time_us=(\d+)$/m', $output, $matches);
        //             if (count($matches) !== 2) {
        //                 return;
        //             }

        //             $bar->setProgress((int) round($matches[1] / 1000));
        //         });

        //         $result = $process->wait();
        //         $bar->finish();
        //         $this->newLine();
        //     }
        // }

        // ffmpeg -i tears-of-steel.mp4 \
        //     -c:a aac \
        //     -ac 2 \
        //     -ar 48000 \
        //     -b:a 128k \
        //     -profile:v high \
        //     -vf scale=w=1920:h=-2 \
        //     -b:v 6000k \
        //     -maxrate 12000k \
        //     -bufsize 12000k \
        //     -pix_fmt yuv420p \
        //     -movflags +faststart \
        //     1080.mp4
    }
}
