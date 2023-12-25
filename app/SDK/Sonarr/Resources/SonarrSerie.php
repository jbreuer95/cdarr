<?php

namespace App\SDK\Sonarr\Resources;

use App\SDK\Sonarr\Exceptions\RemoteException;
use Illuminate\Support\Collection;

class SonarrSerie extends AbstractResource
{
    public function all(): ?Collection
    {
        $response = $this->request->get('series');

        if (! $response->ok()) {
            throw new RemoteException($response->body());
        }

        $series = collect($response->object())->reject(function ($serie) {
            return $serie->statistics->episodeCount === 0;
        });

        return $series;
    }
}
