<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EnpassExport implements FromArray, WithHeadings
{

    protected $entries;

    public function headings(): array
    {
        return [
            'Title',
            'Username',
            'Email',
            'Password',
            'Website',
            "TOTP Secret Key",
            "Custom Field 1",
            "*Custom Field 2",
            "Note",
            "Tags"
        ];
    }

    public function __construct($entries)
    {
        $formatted = [];
        foreach ($entries as $entry) {
            $title = explode('.', $entry['url']);
            $title = $title[count($title) - 2];

            $tag = '';

            if (str_contains($entry['email'] ?? '', '@boekm.nl')) {
                $tag = 'boekm';
            } elseif (str_contains($entry['email'] ?? '', '@nextgear.nl')) {
                $tag = 'nextgear';
            } elseif (str_contains($entry['email'] ?? '', '@printenbind.nl')) {
                $tag = 'printenbind';
            }

            array_push($formatted, [
                ucfirst($title),
                $entry['username'],
                $entry['email'],
                str_replace(["\r", "\n", "\r\n"], "", $entry['password']),
                'https://'.$entry['url'],
                $entry['totp'],
                '',
                '',
                $entry['note'],
                $tag,
            ]);
        }
        $this->entries = $formatted;
    }

    public function array(): array
    {
        return $this->entries;
    }
}
