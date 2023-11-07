<?php

use App\Http\Controllers\DebugController;
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

Route::get('/debug', DebugController::class)->name('debug');

Route::inertia('/', 'HomePage')->name('home');
Route::inertia('/history', 'TodoPage')->name('history');
Route::inertia('/series', 'TodoPage')->name('series');
Route::inertia('/movies', 'TodoPage')->name('movies');

Route::inertia('/settings', 'TodoPage')->name('settings');
Route::inertia('/settings/general', 'TodoPage')->name('settings.general');
Route::inertia('/settings/video', 'TodoPage')->name('settings.video');
Route::inertia('/settings/audio', 'TodoPage')->name('settings.audio');
Route::inertia('/settings/subtitles', 'TodoPage')->name('settings.subtitles');

Route::inertia('/system', 'TodoPage')->name('system');
Route::inertia('/system/status', 'TodoPage')->name('system.status');
Route::inertia('/system/tasks', 'TodoPage')->name('system.tasks');
Route::inertia('/system/backup', 'TodoPage')->name('system.backup');
Route::inertia('/system/updates', 'TodoPage')->name('system.updates');
Route::inertia('/system/events', 'TodoPage')->name('system.events');
Route::inertia('/system/logs', 'TodoPage')->name('system.logs');
