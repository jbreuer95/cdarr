<?php

namespace App\Observers;

use App\Events\TranscodeCreated;
use App\Events\TranscodeFinished;
use App\Events\TranscodeUpdate;
use App\Models\Transcode;

class TranscodeObserver
{
    /**
     * Handle the Transcode "created" event.
     *
     * @param  \App\Models\Transcode  $transcode
     * @return void
     */
    public function created(Transcode $transcode)
    {
        event(new TranscodeCreated($transcode));
    }

    /**
     * Handle the Transcode "updated" event.
     *
     * @param  \App\Models\Transcode  $transcode
     * @return void
     */
    public function updated(Transcode $transcode)
    {
        event(new TranscodeUpdate($transcode));

        $changes = $transcode->getDirty();
        if (isset($changes['status'])) {
            $new = $changes['status'];
            $old = $transcode->getOriginal('status');
            if (in_array($new, ['failed', 'finished']) && in_array($old, ['waiting', 'transcoding'])) {
              event(new TranscodeFinished($transcode));
            }
        }

    }
}
