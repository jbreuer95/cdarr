<?php

namespace App\Facades;

use App\SDK\Radarr\Client;
use Illuminate\Support\Facades\Facade;

class Radarr extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
