<?php

namespace App\Providers;

use App\Models\Transcode;
use App\Observers\TranscodeObserver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (File::exists('/config/app.key')) {
            config(['app.key' => File::get('/config/app.key')]);
        }
        Transcode::observe(TranscodeObserver::class);
    }
}
