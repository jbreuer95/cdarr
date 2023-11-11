<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $casts = [
        'value' => 'array',
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    public static function register()
    {
        $db_settings = Setting::all(['key', 'value'])->keyBy('key')->transform(function ($setting) {
            return $setting->value;
        })->toArray();

        config($db_settings);
    }
}
