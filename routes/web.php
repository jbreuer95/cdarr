<?php

use App\Http\Controllers\DebugController;
use App\Http\Livewire\ShowHistory;
use App\Http\Livewire\ShowMovies;
use App\Http\Livewire\ShowQueue;
use App\Http\Livewire\ShowSeries;
use App\Http\Livewire\ShowSettings;
use App\Http\Livewire\ShowSystem;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    return redirect()->route('queue');
});
Route::get('/debug', [DebugController::class, 'index'])->name('debug');
Route::get('/queue', ShowQueue::class)->name('queue');
Route::get('/history', ShowHistory::class)->name('history');
Route::get('/series', ShowSeries::class)->name('series');
Route::get('/movies', ShowMovies::class)->name('movies');
Route::get('/settings', ShowSettings::class)->name('settings');
Route::get('/settings/general', ShowSettings::class)->name('settings.general');
Route::get('/settings/sonarr', ShowSettings::class)->name('settings.sonarr');
Route::get('/settings/radarr', ShowSettings::class)->name('settings.radarr');
Route::get('/settings/plex', ShowSettings::class)->name('settings.plex');
Route::get('/settings/emby', ShowSettings::class)->name('settings.emby');
Route::get('/settings/jellyfin', ShowSettings::class)->name('settings.jellyfin');
Route::get('/settings/video', ShowSettings::class)->name('settings.video');
Route::get('/settings/audio', ShowSettings::class)->name('settings.audio');
Route::get('/settings/subtitles', ShowSettings::class)->name('settings.subtitles');
Route::get('/settings/scheduler', ShowSettings::class)->name('settings.scheduler');
Route::get('/settings/notifications', ShowSettings::class)->name('settings.notifications');

Route::get('/system', ShowSystem::class)->name('system');
Route::get('/system/status', ShowSystem::class)->name('system.status');
Route::get('/system/tasks', ShowSystem::class)->name('system.tasks');
Route::get('/system/backup', ShowSystem::class)->name('system.backup');
Route::get('/system/updates', ShowSystem::class)->name('system.updates');
Route::get('/system/events', ShowSystem::class)->name('system.events');
Route::get('/system/logs', ShowSystem::class)->name('system.logs');
