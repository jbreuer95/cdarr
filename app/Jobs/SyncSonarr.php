<?php

namespace App\Jobs;

use App\Facades\Sonarr;
use App\Models\Episode;
use App\Models\Event;
use App\Models\Serie;
use App\Models\VideoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncSonarr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 0;

    protected Event $event;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event)
    {
        $this->onQueue('default');

        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->event->info('Started syncing movies with Sonarr');
            $sonarr_series = Sonarr::series()->all();
            $this->event->info('Found '.count($sonarr_series).' series with a video file');

            foreach ($sonarr_series as $sonarr_serie) {
                $serie = Serie::where('sonarr_serie_id', $sonarr_serie->id)->first();
                if (! $serie) {
                    $serie = new Serie();
                    $serie->sonarr_serie_id = $sonarr_serie->id;
                    $serie->title = $sonarr_serie->title;
                    $serie->year = $sonarr_serie->year ?? null;
                    $serie->network = $sonarr_serie->network ?? null;
                    $serie->save();
                }
                $sonarr_episodes = Sonarr::episodes()->all($serie->sonarr_serie_id);
                foreach ($sonarr_episodes as $sonarr_episode) {
                    $episode = Episode::where('sonarr_episode_id', $sonarr_episode->id)->first();
                    if (! $episode) {
                        $episode = new Episode();
                        $episode->serie_id = $serie->id;
                        $episode->sonarr_episode_id = $sonarr_episode->id;
                        $episode->sonarr_file_id = $sonarr_episode->episodeFileId;

                        $episode->season = $sonarr_episode->seasonNumber;
                        $episode->episode = $sonarr_episode->episodeNumber;
                        $episode->quality = $sonarr_episode->episodeFile->quality->quality->name ?? null;
                        $episode->save();

                        $file = new VideoFile();
                        $file->path = $sonarr_episode->episodeFile->path;
                        $file->episode_id = $episode->id;
                        $file->save();

                        $event = new Event();
                        $event->type = (new \ReflectionClass(AnalyzeFile::class))->getShortName();
                        $event->video_file_id = $file->id;
                        $event->info('Queued analyzing file '.pathinfo($file->path, PATHINFO_BASENAME));

                        AnalyzeFile::dispatch($event, $file);
                    }
                }
            }

            $this->event->info('Finished sync with Sonarr');
        } catch (Throwable $th) {
            $this->failed($th);
        }
    }

    public function failed(Throwable $th): void
    {
        $this->event->error('Job failed with the following error:');
        $this->event->error($th->getMessage());
    }
}
