<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $appends = ['title'];

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function videofile()
    {
        return $this->hasOne(VideoFile::class);
    }

    protected function title(): Attribute
    {
        return new Attribute(
            get: function () {
                return  'S'.str($this->season)->padLeft(2, '0') . 'E'. str($this->episode)->padLeft(2, '0');
            },
        );
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
