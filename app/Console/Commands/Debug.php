<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\TranscodeVideo;
use App\Jobs\TranscodeVideoAWS;
use App\Jobs\TranscodeVideoOnline;
use App\Models\Transcode;
use Illuminate\Support\Facades\Storage;

class Debug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $transcode = Transcode::first();
        // TranscodeVideo::dispatch($transcode);
        // TranscodeVideoAWS::dispatchSync($transcode);

        return 0;
    }
}
