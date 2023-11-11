<?php

namespace App\SDK\Radarr;

use App\SDK\Radarr\Resources\RadarrMovie;
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
            'X-Api-Key' => $config['token']
        ])->timeout(60)->baseUrl($config['url']. '/api/v3');
    }

    public function getClient()
    {
        return $this;
    }

    public function movies()
    {
        return new RadarrMovie($this, $this->request);
    }
}
