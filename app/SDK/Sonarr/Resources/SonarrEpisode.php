<?php

namespace App\SDK\Sonarr\Resources;

use App\SDK\Sonarr\Exceptions\RemoteException;
use Illuminate\Support\Collection;

class SonarrEpisode extends AbstractResource
{
    public function all($seriesId): ?Collection
    {
        $response = $this->request->get('episode', [
            'seriesId' => $seriesId
        ]);

        if (! $response->ok()) {
            throw new RemoteException($response->body());
        }

        $episodes = collect($response->object())->reject(function ($serie) {
            return $serie->episodeFileId === 0;
        })->values();

        $response = $this->request->get('episodefile', [
            'seriesId' => $seriesId
        ]);

        if (! $response->ok()) {
            throw new RemoteException($response->body());
        }

        $files = collect($response->object());

        $episodes = $episodes->map(function($episode) use ($files) {
            $episode->episodeFile = $files->where('id', $episode->episodeFileId)->first();

            return $episode;
        });

        return $episodes;
    }
}
