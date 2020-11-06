<?php

namespace App\Http\Controllers;

use App\Jobs\TranscodeVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SonarrController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->all();
        if (!in_array($data['eventType'], ['Rename', 'Grab'])) {
            $path =  $data['episodeFile']['path'];
            $info = pathinfo($path);
            $hidden = $info['dirname'] . '/.' . $info['basename'];

            File::move($path, $hidden);
            $this->dispatch(new TranscodeVideo($hidden));

            return true;
        }

        return false;
    }
}
