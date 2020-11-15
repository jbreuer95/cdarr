<?php

namespace App\Http\Livewire;

use App\Models\Transcode;
use Livewire\Component;
use Livewire\WithPagination;

class ShowQueue extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.show-queue',[
            'transcodes' => Transcode::whereIn('status', ['waiting', 'transcoding'])->paginate(10)
        ]);
    }
}
