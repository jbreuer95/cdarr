<?php

namespace App\SDK\Radarr\Resources;

use App\SDK\Radarr\Exceptions\RemoteException;
use Illuminate\Support\Collection;

class RadarrMovie extends AbstractResource
{
    public function all(): ?Collection
    {
        $response = $this->request->get('movie');

        if (! $response->ok()) {
            throw new RemoteException($response->body());
        }

        $movies = collect($response->object())->reject(function ($movie) {
            return empty($movie->movieFile);
        });

        return $movies;
    }
}
