<?php

namespace App\SDK\Sonarr\Resources;

use App\SDK\Sonarr\Client;
use Illuminate\Http\Client\PendingRequest;

abstract class AbstractResource
{
    protected Client $client;

    protected PendingRequest $request;

    public function __construct(Client $client, PendingRequest $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
