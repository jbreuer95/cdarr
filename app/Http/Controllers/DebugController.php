<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
class DebugController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        dd('test');
    }
}
