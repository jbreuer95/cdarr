@extends('layouts.master')

@section('content')
    <div class="text-black m-5">
        <table class="w-full mb-5">
            <th class="text-left">Episode/Movie</th>
            <th class="text-left">Status</th>
            <th class="text-left">Started</th>
            <th class="text-left">Progress</th>
            @foreach ($transcodes as $transcode)
                    <tr>
                        @if ($transcode->service === 'sonarr')
                            <td>{{ "$transcode->series_title - $transcode->episode_title S{$transcode->season_number}E{$transcode->episode_number}" }}</td>
                        @elseif ($transcode->service === 'radarr'))
                            <td>{{ basename($transcode->path) }}</td>
                        @endif
                        <td>{{ ucfirst($transcode->status) }}</td>
                        @if ($transcode->status === 'transcoding')
                            <td>{{ $transcode->created_at->diffForHumans() }}</td>
                            <td>{{ number_format($transcode->progress, 2) }} %</td>
                        @else
                            <td>---</td>
                            <td>---</td>
                        @endif
                    </tr>
            @endforeach
        </table>
        {{ $transcodes->links() }}
    </div>
@endsection
