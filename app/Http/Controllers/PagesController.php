<?php

namespace App\Http\Controllers;

use App\Models\Transcode;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function queue(Request $request)
    {
        $transcodes = Transcode::whereIn('status', ['waiting', 'transcoding'])->paginate(10);

        return view('queue', compact('transcodes'));
    }

    public function history(Request $request)
    {
        $transcodes = Transcode::whereIn('status', ['failed', 'finished'])->paginate(10);

        return view('history', compact('transcodes'));
    }

    public function series(Request $request)
    {
        return view('series');
    }

    public function movies(Request $request)
    {
        return view('movies');
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
