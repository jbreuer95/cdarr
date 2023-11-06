<?php

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
Route::inertia('/history', 'TodoPage')->name('history');
Route::inertia('/series', 'TodoPage')->name('series');
Route::inertia('/movies', 'TodoPage')->name('movies');
