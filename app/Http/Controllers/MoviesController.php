<?php

namespace App\Http\Controllers;

use App\Jobs\SyncRadarr;
use App\Models\Movie;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MoviesController extends Controller
{
    public function index(Request $request)
    {
        $movies = Movie::orderBy('id')->cursorPaginate(100);
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
        dispatch_sync(new SyncRadarr);

        return response()->json([
            'success' => true,
        ]);
    }
}
