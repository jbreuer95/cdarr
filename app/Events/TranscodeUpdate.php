<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class TranscodeUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $transcode;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($transcode)
    {
        $this->transcode = $transcode;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('public');
    }

    public function broadcastWhen()
    {
        $id = 'transcode-updates.'.$this->transcode->id;

        if (!RateLimiter::tooManyAttempts($id, 1)) {
            RateLimiter::hit($id, 1);

            return true;
        }
        return false;
    }

    public function broadcastWith()
    {
        return ['id' => $this->transcode->id];
    }
}
