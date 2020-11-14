<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcode extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'cmd', 'service', 'webhook_data'];

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function getSeriesTitleAttribute()
    {
        $data = json_decode($this->webhook_data, true);

        return $data['series']['title'];
    }

    public function getEpisodeTitleAttribute()
    {
        $data = json_decode($this->webhook_data, true);

        return $data['episodes'][0]['title'];
    }


    public function getSeasonNumberAttribute()
    {
        $data = json_decode($this->webhook_data, true);

        return str_pad($data['episodes'][0]['seasonNumber'], 2, '0', STR_PAD_LEFT);
    }

    public function getEpisodeNumberAttribute()
    {
        $data = json_decode($this->webhook_data, true);

        return str_pad($data['episodes'][0]['episodeNumber'], 2, '0', STR_PAD_LEFT);
    }

    public function getTranscodeTimeAttribute()
    {
        return $this->updated_at->diffForHumans($this->created_at, CarbonInterface::DIFF_ABSOLUTE);
    }

    public function getProgressAttribute($value)
    {
        return round($value / 100, 2);
    }


}
