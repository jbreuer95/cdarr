<?php

namespace App\Models;

use App\Enums\EncodeStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encode extends Model
{
    use HasFactory;

    protected $appends = ['runtime'];

    protected $casts = [
        'status' => EncodeStatus::class,
        'progress' => 'integer',
    ];

    public function videofile()
    {
        return $this->belongsTo(VideoFile::class, 'video_file_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function runtime(): Attribute
    {
        return new Attribute(
            get: function () {
                if (in_array($this->status, [EncodeStatus::TRANSCODING])) {
                    return $this->created_at->diffForHumans(now(), CarbonInterface::DIFF_ABSOLUTE);
                }
                if (in_array($this->status, [EncodeStatus::WAITING])) {
                    return 0;
                }

                return $this->created_at->diffForHumans($this->updated_at, CarbonInterface::DIFF_ABSOLUTE);
            },
        );
    }
}
