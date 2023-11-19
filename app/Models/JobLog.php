<?php

namespace App\Models;

use App\Enums\JobLogStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLog extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => JobLogStatusEnum::class
    ];
    public function info($line)
    {
        $this->payload .= "$line\n";
        $this->save();
    }

    public function toHtml()
    {
        return nl2br(str_replace(' ', '&nbsp;', $this->payload));
    }
}
