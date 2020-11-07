<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Encryption\Encrypter;
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
        if (is_dir('/config')) {
            // Create database outside docker if it does not exist
            $db_exists = Storage::disk('config')->exists('database.sqlite');
            if (!$db_exists) {
                Storage::disk('config')->put('database.sqlite', '');
            }
            // Setup a APP key outside docker if it does not exist
            $key_exists = Storage::disk('config')->exists('app.key');
            if (!$key_exists) {
                $key = 'base64:'.base64_encode(
                    Encrypter::generateKey(config('app.cipher'))
                );
                Storage::disk('config')->put('app.key', $key);
            }

            config(['app.key' => Storage::disk('config')->get('app.key')]);
            Artisan::call('migrate', [
                '--path' => 'database/migrations',
                '--database' => 'sqlite',
                '--force' => true
            ]);
        }
    }
}
