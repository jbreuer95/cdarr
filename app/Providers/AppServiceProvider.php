<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
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
        $db_exists = Storage::disk('config')->exists('database.sqlite');
        if (!$db_exists) {
            Storage::disk('config')->put('database.sqlite', '');
        }
        Artisan::call('migrate', [
            '--path' => 'database/migrations',
            '--database' => 'sqlite',
            '--force' => true
        ]);
    }
}
