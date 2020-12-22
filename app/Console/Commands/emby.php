<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;

class emby extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emby';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $client = new Client([
            'base_uri' => 'https://url/emby',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Emby-Token' => 'token',
            ]
        ]);
        $users = [''];

        $res = $client->request('GET', '/Users');
        $users = json_decode((string)$res->getBody());
        foreach ($users as $user) {
            if ($user->Name !== 'me') {
                $id = $user->Id;
                $res = $client->request('POST', "/Users/$id/Policy", [RequestOptions::JSON => [
                    "IsHidden" => true,
                    "IsHiddenRemotely" => true,
                    "EnableRemoteAccess" => true,
                    "EnableMediaPlayback" => true,
                    "EnableAllDevices" => true,
                    "EnableAllFolders" => true,
                    "EnableUserPreferenceAccess" => true,
                    "SimultaneousStreamLimit" => 1,

                    "IsAdministrator" => false,
                    "IsDisabled" => false,
                    "IsTagBlockingModeInclusive" => false,
                    "EnableRemoteControlOfOtherUsers" => false,
                    "EnableSharedDeviceControl" => false,
                    "EnableLiveTvManagement" => false,
                    "EnableLiveTvAccess" => false,
                    "EnableAudioPlaybackTranscoding" => false,
                    "EnableVideoPlaybackTranscoding" => false,
                    "EnablePlaybackRemuxing" => false,
                    "EnableContentDeletion" => false,
                    "EnableContentDownloading" => false,
                    "EnableSubtitleDownloading" => false,
                    "EnableSubtitleManagement" => false,
                    "EnableSyncTranscoding" => false,
                    "EnableMediaConversion" => false,
                    "EnableAllChannels" => false,
                    "EnablePublicSharing" => false,
                ]]);
                $res = json_decode((string)$res->getBody());
                $this->info($user->Name);
            }
        }

        return 0;
    }
}
