<?php

namespace App\Http\Livewire;

use Livewire\Component;

class QueueItem extends Component
{
    public $transcode;

    public function render()
    {
        return view('livewire.queue-item');
    }
}
