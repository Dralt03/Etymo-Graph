<?php

use App\Http\Controllers\WordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/search', [WordController::class, 'search'])->name('words.search');
Route::get('/word/{word}', [WordController::class, 'show'])->name('words.show');
