<?php

namespace App\Providers;

use App\SDK\Sonarr\Client;
use Illuminate\Support\ServiceProvider;

class SonarrServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client(config('sonarr'));
        });
    }
}
