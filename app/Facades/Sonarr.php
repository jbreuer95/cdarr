<?php

namespace App\Facades;

use App\SDK\Sonarr\Client;
use Illuminate\Support\Facades\Facade;

class Sonarr extends Facade
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
