<?php

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
Route::get('/', ShowQueue::class)->name('queue');
Route::get('/history', ShowHistory::class)->name('history');
Route::get('/series', ShowSeries::class)->name('series');
Route::get('/movies', ShowMovies::class)->name('movies');
Route::get('/settings', ShowSettings::class)->name('settings');
Route::get('/system', ShowSystem::class)->name('system');
