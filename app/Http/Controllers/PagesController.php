<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function series(Request $request)
    {
        return view('series');
    }

    public function movies(Request $request)
    {
        return view('movies');
    }

    public function queue(Request $request)
    {
        return view('queue');
    }

    public function history(Request $request)
    {
        return view('history');
    }

    public function settings(Request $request)
    {
        return view('settings');
    }

    public function system(Request $request)
    {
        return view('system');
    }
}
