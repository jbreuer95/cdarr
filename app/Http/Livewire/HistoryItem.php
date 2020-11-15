<?php

namespace App\Http\Livewire;

use Livewire\Component;

class HistoryItem extends Component
{
    public $transcode;

    public function render()
    {
        return view('livewire.history-item');
    }
}
