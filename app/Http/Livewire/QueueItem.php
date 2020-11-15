<?php

namespace App\Http\Livewire;

use Livewire\Component;

class QueueItem extends Component
{
    public $transcode;

    protected $listeners = ['echo:public,TranscodeUpdate' => 'updateProgress'];

    public function updateProgress($data)
    {
        if ($data['id'] === $this->transcode->id) {
            $this->transcode->refresh();
        }
    }

    public function render()
    {
        return view('livewire.queue-item');
    }
}
