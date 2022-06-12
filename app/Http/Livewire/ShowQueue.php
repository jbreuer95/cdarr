<?php

namespace App\Http\Livewire;

use App\Models\Transcode;
use Livewire\Component;
use Livewire\WithPagination;

class ShowQueue extends Component
{
    use WithPagination;

    protected $listeners = [
        'echo:public,TranscodeCreated' => 'refreshTable',
        'echo:public,TranscodeFinished' => 'refreshTable'
    ];

    public function refreshTable()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.show-queue',[
            'transcodes' => Transcode::whereNotIn('status', ['failed', 'finished'])->paginate(25)
        ]);
    }
}
