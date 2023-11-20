<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $appends = ['status'];

    public function videofile()
    {
        return $this->hasOne(VideoFile::class);
    }

    protected function status(): Attribute
    {
        return new Attribute(
            get: function ()  {
                if (!$this->videofile->analysed) {
                    return 'Queued for analysing';
                }
                if (!$this->videofile->compliant && !$this->videofile->encoded) {
                    return 'Queued for encoding';
                }
                if (!$this->videofile->compliant && $this->videofile->encoded) {
                    return 'Encoded but not compliant (anymore)';
                }
                if ($this->videofile->compliant && !$this->videofile->encoded) {
                    return 'Compliant, no need for encoding';
                }
                if ($this->videofile->compliant && $this->videofile->encoded) {
                    return 'Encoded and compliant';
                }

                return 'Unknown';
            },
        );
    }
}
