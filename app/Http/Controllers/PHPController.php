<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PHPController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'ctype' => extension_loaded('ctype'),
            'curl' => extension_loaded('curl'),
            'dom' => extension_loaded('dom'),
            'fileinfo' => extension_loaded('fileinfo'),
            'filter' => extension_loaded('filter'),
            'hash' => extension_loaded('hash'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'pcre' => extension_loaded('pcre'),
            'pdo' => extension_loaded('pdo'),
            'session' => extension_loaded('session'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'intl' => extension_loaded('intl'),
            'pcntl' => extension_loaded('pcntl'),
            'posix' => extension_loaded('posix'),
        ]);
    }
}
