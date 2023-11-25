<?php

namespace App\Http\Controllers;

use App\Enums\EncodeStatus;
use App\Models\Encode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $encodes = Encode::select('id', 'status', 'progress', 'video_file_id', 'updated_at', 'created_at')
            ->with('videofile:id,movie_id,path', 'videofile.movie:id,title,year')
            ->whereIn('status', [EncodeStatus::WAITING, EncodeStatus::TRANSCODING])
            ->orderByDesc('updated_at')
            ->cursorPaginate(100);

        $encodes = $encodes->through(function ($encode) {
            if (! empty($encode->videofile->path)) {
                $encode->videofile->path = pathinfo($encode->videofile->path, PATHINFO_BASENAME);
            }

            return $encode;
        });

        if ($request->wantsJson()) {
            return $encodes;
        }

        return Inertia::render('QueuePage', [
            'type' => 'queue',
            'encodes' => $encodes,
        ]);
    }

    public function history(Request $request)
    {
        $encodes = Encode::select('id', 'status', 'progress', 'video_file_id', 'updated_at', 'created_at')
            ->with('videofile:id,movie_id,path', 'videofile.movie:id,title,year')
            ->whereNotIn('status', [EncodeStatus::WAITING, EncodeStatus::TRANSCODING])
            ->orderByDesc('id')
            ->cursorPaginate(100);

        $encodes = $encodes->through(function ($encode) {
            if (! empty($encode->videofile->path)) {
                $encode->videofile->path = pathinfo($encode->videofile->path, PATHINFO_BASENAME);
            }

            return $encode;
        });

        if ($request->wantsJson()) {
            return $encodes;
        }

        return Inertia::render('QueuePage', [
            'type' => 'history',
            'encodes' => $encodes,
        ]);
    }
}
