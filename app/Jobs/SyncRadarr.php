<?php

namespace App\Jobs;

use App\Facades\Radarr;
use App\Models\Movie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncRadarr implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $radarr_movies = Radarr::movies()->all();

        foreach ($radarr_movies as $radarr_movie) {
            $movie = Movie::where('radarr_movie_id', $radarr_movie->id)->first();
            if (! $movie) {
                $movie = new Movie();
                $movie->radarr_movie_id = $radarr_movie->id;
                $movie->radarr_file_id = $radarr_movie->movieFile->id;
                $movie->path = $radarr_movie->movieFile->path;
                $movie->title = $radarr_movie->title;
                $movie->year = $radarr_movie->year ?? null;
                $movie->studio = $radarr_movie->studio ?? null;
                $movie->quality = $radarr_movie->movieFile->quality->quality->resolution ?? null;
                $movie->status = 'unknown';
                $movie->save();
            } else {
                // TODO
            }
        }
    }
}
