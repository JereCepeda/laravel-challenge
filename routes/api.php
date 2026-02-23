<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\CheckerApiController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/invitations/{hash}/redeem', [TicketController::class, 'redeemInvitation'])
    ->where('hash', '[a-z0-9]{6}');


    Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/{section}', [DashboardController::class, 'loadSection'])
        ->where('section', '[a-z\-]+');
    
    // Tickets
    Route::post('/tickets/validate', [TicketController::class, 'validateTicket']);
    
    // Admin APIs
    Route::prefix('admin')->middleware('admin:admin')->group(function () {

        Route::get('/tickets/used/{event_name}', [AdminApiController::class, 'getUsedTickets']);
        Route::get('/tickets/used', [AdminApiController::class, 'getUsedTickets']);
        

        Route::get('/redemptions/history', [AdminApiController::class, 'getRedemptionsHistory']);
        

        Route::get('/statistics', [AdminApiController::class, 'getStatistics']);
        Route::get('/reports', [AdminApiController::class, 'getReports']);
    });

    Route::prefix('checker')->middleware('admin:admin,checker')->group(function () {
        Route::get('/events', [CheckerApiController::class, 'getActiveEvents']);
        Route::get('/stats/today', [CheckerApiController::class, 'getTodayStats']);
        Route::get('/history', [CheckerApiController::class, 'getValidationHistory']);
    });
});