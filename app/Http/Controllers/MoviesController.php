<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MoviesController extends Controller
{
    public function index()
    {
        $setup = config('radarr.token') && config('radarr.url');
        return Inertia::render('MoviesPage', [
            'setup' => $setup,
        ]);
    }
}
