<?php

namespace App\Observers;

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
        //
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
    }

    /**
     * Handle the Transcode "deleted" event.
     *
     * @param  \App\Models\Transcode  $transcode
     * @return void
     */
    public function deleted(Transcode $transcode)
    {
        //
    }

    /**
     * Handle the Transcode "restored" event.
     *
     * @param  \App\Models\Transcode  $transcode
     * @return void
     */
    public function restored(Transcode $transcode)
    {
        //
    }

    /**
     * Handle the Transcode "force deleted" event.
     *
     * @param  \App\Models\Transcode  $transcode
     * @return void
     */
    public function forceDeleted(Transcode $transcode)
    {
        //
    }
}
