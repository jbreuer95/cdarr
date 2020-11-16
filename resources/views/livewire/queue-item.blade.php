<tr>
    @if ($transcode->service === 'sonarr')
        <td>{{ "$transcode->series_title - S{$transcode->season_number}E{$transcode->episode_number} - $transcode->episode_title" }}</td>
    @elseif ($transcode->service === 'radarr')
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
