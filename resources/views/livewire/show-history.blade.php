<div class="text-black m-5">
    <table class="w-full mb-5">
        <th class="text-left">Episode/Movie</th>
        <th class="text-left">Status</th>
        <th class="text-left">Started</th>
        <th class="text-left">Finished</th>
        <th class="text-left">Transcode time</th>
        @foreach ($transcodes as $transcode)
            <livewire:history-item :transcode="$transcode" :key="$transcode->id"/>
        @endforeach
    </table>
    {{ $transcodes->links('vendor/pagination/tailwind') }}
</div>
