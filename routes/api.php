<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\CheckerApiController;

// Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/invitations/{hash}/redeem', [TicketController::class, 'redeemInvitation'])
    ->where('hash', '[a-z0-9]{6}');

// Authenticated
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    // Tickets - Rutas originales para los tests
    Route::post('/tickets/validate', [TicketController::class, 'validateTicket']);
    
    // Admin - Rutas originales para los tests (solo admin)
    Route::prefix('admin')->middleware('admin:admin')->group(function () {
        Route::get('/tickets/used/{event_name}', [AdminController::class, 'getUsedTickets']);
        Route::get('/redemption/history', [AdminController::class, 'getRedemptionHistory']);
    });
});