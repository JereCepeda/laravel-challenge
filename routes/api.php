<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return auth('api')->check() ? view('panel') : view('auth.login');
});

// Auth routes
Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('api.auth.logout');

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'me'])->name('api.user');
});