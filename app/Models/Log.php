<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = ['transcode_id', 'body'];

    public function transcode()
    {
        return $this->belongsTo(Transcode::class);
    }
}
