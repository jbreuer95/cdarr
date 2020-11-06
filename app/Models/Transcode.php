<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcode extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'cmd'];

    public function logs()
    {
        return $this->hasMany(Log::class);
    }
}
