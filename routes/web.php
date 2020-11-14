<?php

use App\Http\Controllers\PagesController;
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
Route::get('/', [PagesController::class, 'queue'])->name('queue');
Route::get('/history', [PagesController::class, 'history'])->name('history');
Route::get('/series', [PagesController::class, 'series'])->name('series');
Route::get('/movies', [PagesController::class, 'movies'])->name('movies');
Route::get('/settings', [PagesController::class, 'settings'])->name('settings');
Route::get('/system', [PagesController::class, 'system'])->name('system');
