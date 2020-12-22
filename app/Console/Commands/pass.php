<?php

namespace App\Console\Commands;

use App\Exports\EnpassExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class pass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pass';

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

    public function exportCsv($entries)
    {
        $export = new EnpassExport($entries);

        Excel::store($export, 'pass.csv', null, \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = storage_path() . "/pass.json";
        $data = json_decode(file_get_contents($path), true, 20000);
        $items = $data['items'];

        $entries = [];
        $missing = [];
        foreach ($items as $item) {
            if (in_array($item['category'], ['login','computer'])) {
                $entry = [
                    'url' => null,
                    'username' => null,
                    'email' => null,
                    'password' => null,
                    'totp' => null,
                    'note' => null,
                ];
                foreach ($item['fields'] as $field) {
                    if ($field['type'] === 'username' && $field['value']) {
                        if (!!filter_var($field['value'], FILTER_VALIDATE_EMAIL)) {
                            $entry['email'] = $field['value'];
                        } else {
                            $entry['username'] = $field['value'];
                        }
                    }
                    if ($field['type'] === 'email' && $field['value'] && !!filter_var($field['value'], FILTER_VALIDATE_EMAIL)) {
                        $entry['email'] = $field['value'];
                    }
                    if ($field['type'] === 'password' && $field['value']) {
                        $entry['password'] = $field['value'];
                    }
                    if ($field['type'] === 'totp' && $field['value']) {
                        $entry['totp'] = $field['value'];
                    }
                    if ($field['type'] === 'url' && $field['value'] && !!filter_var($field['value'], FILTER_VALIDATE_URL)) {
                        $entry['url'] =  str_replace('www.', '', parse_url($field['value'], PHP_URL_HOST));
                    }
                }
                if ($item['note'] && !in_array($item['note'], ["\r", "\n", "\r\n"])) {
                    $entry['note'] = $item['note'];
                }
                if (!$entry['url'] || (!$entry['username'] && !$entry['email']) || !$entry['password']) {
                    array_push($missing, $entry);
                } else {
                    array_push($entries, $entry);
                }
            }
        }
        $this->info('entries: '. count($entries));
        $this->info('missing: '. count($missing));

        $duplicates = 0;

        foreach ($entries as $current_key => $current_array) {
            foreach ($entries as $search_key => $search_array) {
                if (
                    $search_array['password'] === $current_array['password']
                    && $search_array['url'] === $current_array['url']
                    && $search_array['username'] === $current_array['username']
                    && $search_array['email'] === $current_array['email']
                ) {
                    if ($search_key != $current_key) {
                        $duplicates++;
                        unset($entries[$search_key]);
                    }
                }
            }
        }

        $this->info('duplicates: '. $duplicates);
        $this->info('entries: '. count($entries));

        $this->exportCsv($entries);

        return 0;
    }
}
