<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSonarr;
use App\Models\Event;
use App\Models\Serie;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $series = Serie::with('episodes.videofile.audiostreams')->orderBy('id')->cursorPaginate(100);

        $series = $series->through(function ($serie) {
            $serie->episodeCount = $serie->episodes->count();
            $serie->episodePlayableCount = 0;
            foreach ($serie->episodes as $episode) {
                if ($episode->videofile->playable) {
                    $serie->episodePlayableCount += 1;
                }
            }

            return $serie;
        });

        if ($request->wantsJson()) {
            return $series;
        }

        $setup = config('sonarr.token') && config('sonarr.url');

        return Inertia::render('SeriesPage', [
            'setup' => $setup,
            'series' => $series,
        ]);
    }

    public function sync(Request $request)
    {
        $event = new Event();
        $event->type = (new \ReflectionClass(SyncSonarr::class))->getShortName();
        $event->info('Queued syncing series with Sonarr');

        dispatch_sync(new SyncSonarr($event));

        return response()->json([
            'success' => true,
        ]);
    }
}
