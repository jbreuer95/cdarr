<?php

namespace App\Http\Controllers;

use App\Models\JobLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EventsContoller extends Controller
{
    public function index(Request $request)
    {
        $events = JobLog::select('id', 'updated_at', 'type', DB::raw('SUBSTR(payload, 1, INSTR(payload, char(10)) -1) as firstline'))
            ->orderByDesc('updated_at')
            ->cursorPaginate(100);

        $events = $events->through(function($event) {
            $event->date = $event->updated_at->format('d-m-Y');
            $event->time = $event->updated_at->format('H:i');

            return $event;
        });

        if ($request->wantsJson()) {
            return $events;
        }

        return Inertia::render('System/EventsPage', [
            'events' => $events
        ]);
    }

    public function show(Request $request, $id)
    {
        $event = JobLog::find($id);
        $event->html = $event->toHtml();

        return response()->json($event);
    }

    public function clear(Request $request)
    {
        JobLog::query()->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
