<?php

namespace App\Jobs;

use App\Models\Transcode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Aws\S3\MultipartUploader;
use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Aws\Result;
use Aws\Credentials\Credentials;
use Aws\Sqs\SqsClient;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class TranscodeVideoAWS implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;
    public $timeout = 0;

    protected $transcode;
    protected $client;
    protected $bucket;
    protected $region;
    protected $input;
    protected $output;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transcode $transcode)
    {
        $this->transcode = $transcode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->transcode->status = 'starting';
        $this->transcode->created_at = Date::now();
        $this->transcode->save();

        $this->setup();
        $this->upload();
        $this->encode();
        $this->download();

        $this->transcode->status = 'finished';
        $this->transcode->save();
    }

    public function setup()
    {
        $fs = Storage::disk('s3');
        $driver = $fs->getDriver();
        $adapter = $driver->getAdapter();

        $this->client = $adapter->getClient();
        $this->bucket = $adapter->getBucket();
        $this->region = $this->client->getRegion();
        $this->key = $this->client->getCredentials()->wait()->getAccessKeyId();
        $this->secret = $this->client->getCredentials()->wait()->getSecretKey();
        $this->account = config('services.mediaconvert.account');

        $info = pathinfo($this->transcode->path);

        $process = new Process(['sha256sum', $this->transcode->path]);
        $process->setTimeout(300);
        $process->setIdleTimeout(300);
        $process->run();

        $sum = $process->getOutput();
        $sum = explode(' ', $sum)[0];

        $this->input = $sum . '.' . $info['extension'];
        $this->output = $sum . '-transcoded.mp4';

        return true;
    }

    public function upload()
    {
        if (Storage::disk('s3')->exists($this->input)) {
            return true;
        }

        $this->transcode->status = 'uploading';
        $this->transcode->progress = 0;
        $this->transcode->save();

        $source = fopen($this->transcode->path, 'rb');

        $progress = 0;
        $total = File::size($this->transcode->path);
        $lastUpdate = Date::now();
        $uploader = new MultipartUploader($this->client, $source, [
            'bucket' => $this->bucket,
            'key' => $this->input,
            'params' => [
                '@http' => [
                    'progress' => function ($expectedDl, $dl, $expectedUl, $ul) use (&$progress, &$lastUpdate, &$uploader, $total) {
                        $state = $uploader->getState();
                        $uploaded = $state->getPartSize() * count($state->getUploadedParts());

                        $progress = round($uploaded / $total * 100, 2);
                        $progress = $progress <= 100.00 ? $progress : 100.00;
                        $progress = intval($progress * 100);
                        if ($this->transcode->getRawOriginal('progress') !== $progress) {
                            if ($lastUpdate->diffInMilliseconds(Date::now()) > 100) {
                                $this->transcode->progress = $progress;
                                $this->transcode->save();

                                $lastUpdate = Date::now();
                            }
                        }
                    }
                ]
            ]
        ]);

        $uploader->upload();
        fclose($source);

        $this->transcode->progress = 10000;
        $this->transcode->save();

        return true;
    }

    public function encode()
    {
        if (Storage::disk('s3')->exists($this->output)) {
            return true;
        }

        $this->transcode->status = 'transcoding';
        $this->transcode->progress = 0;
        $this->transcode->save();

        $client = new MediaConvertClient([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ]
        ]);
        $result = $client->describeEndpoints([]);

        $client = new MediaConvertClient([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ],
            'endpoint' => $result['Endpoints'][0]['Url']
        ]);

        $output = pathinfo($this->output, PATHINFO_FILENAME);

        $settings = [
            "Inputs" => [
                [
                    "TimecodeSource" => "ZEROBASED",
                    "VideoSelector" => [
                    ],
                    "AudioSelectors" => [
                        "Audio Selector 1" => [
                            "DefaultSelection" => "DEFAULT"
                        ]
                    ],
                    "FileInput" => "s3://{$this->bucket}/{$this->input}"
                ]
            ],
            "OutputGroups" => [
                [
                    "Name" => "File Group",
                    "OutputGroupSettings" => [
                        "Type" => "FILE_GROUP_SETTINGS",
                        "FileGroupSettings" => [
                            "Destination" => "s3://{$this->bucket}/{$output}"
                        ]
                    ],
                    "Outputs" => [
                        [
                            "VideoDescription" => [
                                "CodecSettings" => [
                                    "Codec" => "H_264",
                                    "H264Settings" => [
                                        "RateControlMode" => "QVBR",
                                        "SceneChangeDetect" => "TRANSITION_DETECTION",
                                        "MaxBitrate" => 5000000,
                                        "FramerateControl" => "SPECIFIED",
                                        "FramerateNumerator" => 30,
                                        "FramerateDenominator" => 1,
                                        "FramerateConversionAlgorithm" => "DUPLICATE_DROP",
                                        "CodecProfile" => "MAIN",
                                        "CodecLevel" => "LEVEL_4"
                                    ]
                                ],
                                "Width" => 1920,
                                "Height" => 1080
                            ],
                            "AudioDescriptions" => [
                                [
                                    "CodecSettings" => [
                                        "Codec" => "AAC",
                                        "AacSettings" => [
                                            "Bitrate" => 128000,
                                            "CodingMode" => "CODING_MODE_2_0",
                                            "SampleRate" => 44100
                                        ]
                                    ],
                                    "AudioSourceName" => "Audio Selector 1"
                                ]
                            ],
                            "ContainerSettings" => [
                                "Container" => "MP4",
                                "Mp4Settings" => [
                                    "MoovPlacement" => "PROGRESSIVE_DOWNLOAD"
                                ]
                            ],
                            "Extension" => "mp4"
                        ]
                    ]
                ]
            ],
            "TimecodeConfig" => [
                "Source" => "ZEROBASED"
            ]
        ];

        $result = $client->createJob([
            "Role" => "arn:aws:iam::{$this->account}:role/service-role/MediaConvert_Default_Role",
            "Settings" => $settings,
            "StatusUpdateInterval" => "SECONDS_10",
            "Queue" => "arn:aws:mediaconvert:eu-west-1:{$this->account}:queues/Default"
        ]);

        $jobID = $result->get('Job')['Id'];

        $completed = false;
        while ($completed === false) {
            sleep(5);

            $result = $client->getJob(['Id' => $jobID]);
            $info = $result->get('Job');

            if (!empty($info['JobPercentComplete'])) {
                $this->transcode->progress = intval($info['JobPercentComplete'] * 100);
                $this->transcode->save();
            }

            if (!empty($info['Status']) && $info['Status'] === 'COMPLETE') {
                $completed = true;
            }
        }

        $this->transcode->progress = 10000;
        $this->transcode->save();

        return true;
    }

    public function download()
    {
        $this->transcode->status = 'downloading';
        $this->transcode->progress = 0;
        $this->transcode->save();

        $info = pathinfo($this->transcode->path);
        $tmp = $info['dirname'] . '/' . $info['filename'] . '-downloading.mp4';
        $final = $info['dirname'] . '/' . ltrim($info['filename'], '.') . '.mp4';

        $url = Storage::disk('s3')->temporaryUrl(
            $this->output,
            now()->addMinutes(60)
        );

        $progress = 0;
        $lastUpdate = Date::now();
        Http::sink($tmp)->withOptions([
            'progress' => function ($dl_total_size, $dl_size_so_far, $ul_total_size, $ul_size_so_far) use (&$progress, &$lastUpdate) {
                if ($dl_size_so_far && $dl_total_size) {
                    $progress = round($dl_size_so_far / $dl_total_size * 100, 2);
                    $progress = $progress <= 100.00 ? $progress : 100.00;
                    $progress = intval($progress * 100);
                    if ($this->transcode->getRawOriginal('progress') !== $progress) {
                        if ($lastUpdate->diffInMilliseconds(Date::now()) > 100) {
                            $this->transcode->progress = $progress;
                            $this->transcode->save();

                            $lastUpdate = Date::now();
                        }
                    }
                }
            },
        ])->get($url);

        $this->transcode->progress = 10000;
        $this->transcode->save();

        File::move($tmp, $final);

        File::delete($this->transcode->path);
        Storage::disk('s3')->delete([
            $this->input,
            $this->output,
        ]);

        return true;
    }
}
