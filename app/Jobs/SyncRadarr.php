<?php

namespace App\Jobs;

use App\Facades\Radarr;
use App\Models\Event;
use App\Models\Movie;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncRadarr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 0;

    protected Event $event;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event)
    {
        $this->onQueue('default');

        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->event->info('Started syncing movies with Radarr');
            $radarr_movies = Radarr::movies()->all();
            $this->event->info('Found '.count($radarr_movies).' movies with a video file');

            foreach ($radarr_movies as $radarr_movie) {
                $movie = Movie::where('radarr_movie_id', $radarr_movie->id)->first();
                if (! $movie) {
                    $movie = new Movie();
                    $movie->radarr_movie_id = $radarr_movie->id;
                    $movie->radarr_file_id = $radarr_movie->movieFile->id;
                    $movie->title = $radarr_movie->title;
                    $movie->year = $radarr_movie->year ?? null;
                    $movie->studio = $radarr_movie->studio ?? null;
                    $movie->quality = $radarr_movie->movieFile->quality->quality->resolution ?? null;
                    $movie->save();

                    $file = new VideoFile();
                    $file->path = $radarr_movie->movieFile->path;
                    $file->movie_id = $movie->id;
                    $file->save();

                    $event = new Event();
                    $event->type = (new \ReflectionClass(AnalyzeFile::class))->getShortName();
                    $event->video_file_id = $file->id;
                    $event->info('Queued analyzing file '.pathinfo($file->path, PATHINFO_BASENAME));

                    AnalyzeFile::dispatch($event, $file);
                }
            }

            $this->event->info('Finished sync with Radarr');
        } catch (Throwable $th) {
            $this->failed($th);
        }
    }

    public function failed(Throwable $th): void
    {
        $this->event->error('Job failed with the following error:');
        $this->event->error($th->getMessage());
    }
}
