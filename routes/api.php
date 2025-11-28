<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return auth('api')->check() ? view('panel') : view('auth.login');
});

Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('api.auth.logout');

Route::post('/invitations/{hash}/redeem', [TicketController::class, 'redeemInvitation']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'me'])->name('api.user');
    
    Route::post('/tickets/validate', [TicketController::class, 'validateTicket']);
});