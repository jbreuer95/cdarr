<?php

use App\Http\Controllers\DebugController;
use App\Http\Controllers\EventsContoller;
use App\Http\Controllers\MoviesController;
use App\Http\Controllers\PHPController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RadarrController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\SonarrController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [QueueController::class, 'index'])->name('queue');
Route::get('/history', [QueueController::class, 'history'])->name('history');
Route::get('/debug', DebugController::class)->name('debug');
Route::get('/php', PHPController::class)->name('php');

Route::get('/movies', [MoviesController::class, 'index'])->name('movies');
Route::post('/movies/sync', [MoviesController::class, 'sync'])->name('movies.sync');
Route::get('/series', [SeriesController::class, 'index'])->name('series');
Route::post('/series/sync', [SeriesController::class, 'sync'])->name('series.sync');
Route::inertia('/settings', 'SettingsPage')->name('settings');
Route::get('/settings/radarr', [RadarrController::class, 'index'])->name('settings.radarr');
Route::put('/settings/radarr', [RadarrController::class, 'update'])->name('settings.radarr.update');
Route::post('/settings/radarr/test', [RadarrController::class, 'test'])->name('settings.radarr.test');
Route::get('/settings/sonarr', [SonarrController::class, 'index'])->name('settings.sonarr');
Route::put('/settings/sonarr', [SonarrController::class, 'update'])->name('settings.sonarr.update');
Route::post('/settings/sonarr/test', [SonarrController::class, 'test'])->name('settings.sonarr.test');
Route::get('/system/events', [EventsContoller::class, 'index'])->name('system.events');
Route::get('/events/{id}', [EventsContoller::class, 'show'])->name('events.show');
Route::post('/events/clear', [EventsContoller::class, 'clear'])->name('events.clear');

Route::inertia('/settings/general', 'TodoPage')->name('settings.general');
Route::inertia('/settings/video', 'TodoPage')->name('settings.video');
Route::inertia('/settings/audio', 'TodoPage')->name('settings.audio');
Route::inertia('/settings/subtitles', 'TodoPage')->name('settings.subtitles');
Route::inertia('/system', 'TodoPage')->name('system');
Route::inertia('/system/status', 'TodoPage')->name('system.status');
Route::inertia('/system/tasks', 'TodoPage')->name('system.tasks');
Route::inertia('/system/backup', 'TodoPage')->name('system.backup');
Route::inertia('/system/updates', 'TodoPage')->name('system.updates');
Route::inertia('/system/logs', 'TodoPage')->name('system.logs');
