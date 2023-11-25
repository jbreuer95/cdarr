<?php

namespace App\Http\Controllers;

use App\Jobs\SyncRadarr;
use App\Models\AudioStream;
use App\Models\Encode;
use App\Models\Event;
use App\Models\Movie;
use App\Models\VideoFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MoviesController extends Controller
{
    public function index(Request $request)
    {
        $movies = Movie::with('videofile.audiostreams')->orderBy('id')->cursorPaginate(100);
        $movies = $movies->through(function ($movie) {
            $movie->append('status');

            return $movie;
        });

        if ($request->wantsJson()) {
            return $movies;
        }

        $setup = config('radarr.token') && config('radarr.url');

        return Inertia::render('MoviesPage', [
            'setup' => $setup,
            'movies' => $movies,
        ]);
    }

    public function sync(Request $request)
    {
        // DB::table('jobs')->delete();
        // Encode::query()->delete();
        // Movie::query()->delete();
        // VideoFile::query()->delete();
        // AudioStream::query()->delete();
        // Event::query()->delete();

        $event = new Event();
        $event->type = (new \ReflectionClass(SyncRadarr::class))->getShortName();
        $event->info('Queued syncing movies with Radarr');

        dispatch_sync(new SyncRadarr($event));

        return response()->json([
            'success' => true,
        ]);
    }
}
