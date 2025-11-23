<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('home');
});

Route::get('/auth', function() {
    return view('auth');
});

Route::get('/auth', [AuthController::class, 'showAuth'])->name('auth.form');
Route::post('/auth', [AuthController::class, 'processAuth'])->name('auth.process');
