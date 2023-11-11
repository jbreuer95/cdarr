<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Setting::where('key', 'radarr')->count() === 0) {
            Setting::create(['key' => 'radarr', 'value' => [
                'url' => '',
                'token' => '',
            ]]);
        }
        if (Setting::where('key', 'sonarr')->count() === 0) {
            Setting::create(['key' => 'sonarr', 'value' => [
                'url' => '',
                'token' => '',
            ]]);
        }
    }
}
