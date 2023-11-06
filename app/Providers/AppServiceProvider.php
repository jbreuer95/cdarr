<?php

namespace App\Providers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $key_path = is_dir('/config') ? '/config/app.key' : storage_path('app/app.key');
        if (! File::exists($key_path)) {
            $key = $this->generateRandomKey();
            File::put($key_path, $key);
        }

        config(['app.key' => File::get($key_path)]);

        $db_path = is_dir('/config') ? '/config/database.sqlite' : database_path('database.sqlite');
        config(['database.connections.sqlite.database' => $db_path]);

        if (! File::exists($db_path)) {
            File::put($db_path, '');
            Artisan::call('migrate --force');
            Artisan::call('db:seed --force');
        }

    }

    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey(config('app.cipher'))
        );
    }
}
