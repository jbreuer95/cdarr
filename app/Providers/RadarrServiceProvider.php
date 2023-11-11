<?php

namespace App\Providers;

use App\SDK\Radarr\Client;
use Illuminate\Support\ServiceProvider;

class RadarrServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client(config('radarr'));
        });
    }
}
