<?php

namespace App\SDK\Sonarr;

use App\SDK\Sonarr\Resources\SonarrEpisode;
use App\SDK\Sonarr\Resources\SonarrSerie;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Client
{
    protected PendingRequest $request;

    public function __construct(array $config)
    {
        $this->request = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-type' => 'application/json',
            'X-Api-Key' => $config['token'],
        ])->timeout(60)->baseUrl($config['url'].'/api/v3');
    }

    public function getClient()
    {
        return $this;
    }

    public function series()
    {
        return new SonarrSerie($this, $this->request);
    }

    public function episodes()
    {
        return new SonarrEpisode($this, $this->request);
    }
}
