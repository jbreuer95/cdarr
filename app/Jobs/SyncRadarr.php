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

    protected ?Event $event = null;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->event = new Event();
        $this->event->type = (new \ReflectionClass($this))->getShortName();

        try {
            $this->event->info('Syncing movies with Radarr');
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

                    AnalyzeFile::dispatch($file);
                }
            }

            $this->event->info('Finished sync with Radarr');
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
        if (! $event) {
            $event = Event::where('type', (new \ReflectionClass($this))->getShortName())
                ->orderByDesc('id')
                ->first();
        }
        if ($event) {
            $event->error('Job failed with the following error:');
            $event->error($th->getMessage());
        }
    }
}
