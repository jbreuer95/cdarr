<?php

namespace App\Http\Controllers;

use App\Jobs\TranscodeVideo;
use App\Models\Transcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class WebhookController extends Controller
{
    public function sonarr(Request $request)
    {
        $data = $request->all();
        if (in_array($data['eventType'], ['Download', 'Upgrade'])) {
            $path =  $data['series']['path'] . '/' . $data['episodeFile']['relativePath'];

            if (File::exists($path)) {
                $transcode = Transcode::create([
                    'path' => $path,
                    'service' => 'sonarr',
                    'webhook_data' => json_encode($data)
                ]);
                $this->initiateTranscode($path, $transcode);

                return response()->json(['succes' => true]);
            }
        }

        return response()->json(['succes' => false]);
    }

    public function radarr(Request $request)
    {
        $data = $request->all();
        if (in_array($data['eventType'], ['Download', 'Upgrade'])) {
            $path =  $data['movie']['folderPath'] . '/' . $data['movieFile']['relativePath'];

            if (File::exists($path)) {
                $transcode = Transcode::create([
                    'path' => $path,
                    'service' => 'radarr',
                    'webhook_data' => json_encode($data)
                ]);
                $this->initiateTranscode($path, $transcode);

                return response()->json(['succes' => true]);
            }
        }

        return response()->json(['succes' => false]);
    }

    protected function initiateTranscode($path, $transcode)
    {
        $info = pathinfo($path);
        $hidden = $info['dirname'] . '/.' . $info['basename'];

        File::move($path, $hidden);
        $transcode->logs()->create(['body' => "Moved $path to $hidden"]);
        $this->dispatch(new TranscodeVideo($hidden, $transcode));
    }
}
