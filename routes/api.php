<?php

use App\Http\Controllers\RadarrController;
use App\Http\Controllers\SonarrController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/sonarr', [SonarrController::class, 'webhook']);
Route::post('/radarr', [RadarrController::class, 'webhook']);
