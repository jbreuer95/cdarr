<tr>
    @if ($transcode->service === 'sonarr')
        <td>{{ "$transcode->series_title - $transcode->episode_title S{$transcode->season_number}E{$transcode->episode_number}" }}</td>
    @elseif ($transcode->service === 'radarr'))
        <td>{{ basename($transcode->path) }}</td>
    @endif

    <td>{{ ucfirst($transcode->status) }}</td>
    <td>{{ $transcode->created_at->diffForHumans() }}</td>

    @if ($transcode->status !== 'failed')
        <td>{{ $transcode->updated_at->diffForHumans() }}</td>
        <td>{{ $transcode->transcode_time }}</td>
    @else
        <td>---</td>
        <td>---</td>
    @endif
</tr>
