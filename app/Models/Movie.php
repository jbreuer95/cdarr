<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    public function videofile()
    {
        return $this->hasOne(VideoFile::class);
    }

    protected function status(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->videofile->status;
            },
        );
    }
}
