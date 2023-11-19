<?php

use App\Http\Controllers\DebugController;
use App\Http\Controllers\EventsContoller;
use App\Http\Controllers\MoviesController;
use App\Http\Controllers\RadarrController;
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

Route::inertia('/', 'HomePage')->name('home');
Route::get('/debug', DebugController::class)->name('debug');

Route::get('/movies', [MoviesController::class, 'index'])->name('movies');
Route::post('/movies/sync', [MoviesController::class, 'sync'])->name('movies.sync');
Route::get('/settings/radarr', [RadarrController::class, 'index'])->name('settings.radarr');
Route::put('/settings/radarr', [RadarrController::class, 'update'])->name('settings.radarr.update');
Route::post('/settings/radarr/test', [RadarrController::class, 'test'])->name('settings.radarr.test');
Route::get('/system/events', [EventsContoller::class, 'index'])->name('system.events');
Route::get('/events/{id}', [EventsContoller::class, 'show'])->name('events.show');
Route::post('/events/clear', [EventsContoller::class, 'clear'])->name('events.clear');

Route::inertia('/history', 'TodoPage')->name('history');
Route::inertia('/series', 'TodoPage')->name('series');
Route::inertia('/settings', 'TodoPage')->name('settings');
Route::inertia('/settings/general', 'TodoPage')->name('settings.general');
Route::inertia('/settings/video', 'TodoPage')->name('settings.video');
Route::inertia('/settings/audio', 'TodoPage')->name('settings.audio');
Route::inertia('/settings/subtitles', 'TodoPage')->name('settings.subtitles');
Route::inertia('/settings/sonarr', 'TodoPage')->name('settings.sonarr');
Route::inertia('/system', 'TodoPage')->name('system');
Route::inertia('/system/status', 'TodoPage')->name('system.status');
Route::inertia('/system/tasks', 'TodoPage')->name('system.tasks');
Route::inertia('/system/backup', 'TodoPage')->name('system.backup');
Route::inertia('/system/updates', 'TodoPage')->name('system.updates');
Route::inertia('/system/logs', 'TodoPage')->name('system.logs');
