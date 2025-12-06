<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\CheckerApiController;

Route::get('/', fn() => view('welcome'));

// Authentication
Route::get('/login', [AuthController::class, 'index'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');

// Dashboard (autenticado con session)
Route::middleware('auth')->prefix('dashboard')->group(function () {
    // Vista principal (carga layout con sidebar dinámico)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Cargar vistas parciales SPA (sin recargar página)
    Route::get('/{section}', [DashboardController::class, 'loadSection'])
        ->where('section', '[a-z\-]+')
        ->name('dashboard.section');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // API endpoints para el dashboard (con autenticación web/sesión)
    Route::prefix('api')->group(function () {
        // Admin APIs
        Route::middleware('admin:admin')->prefix('admin')->group(function () {
            Route::get('/statistics', [AdminApiController::class, 'getStatistics']);
            Route::get('/tickets/used', [AdminApiController::class, 'getUsedTickets']);
            Route::get('/redemptions/history', [AdminApiController::class, 'getRedemptionsHistory']);
            Route::get('/reports', [AdminApiController::class, 'getReports']);
        });
        
        // Checker APIs
        Route::middleware('admin:admin,checker')->prefix('checker')->group(function () {
            Route::get('/events', [CheckerApiController::class, 'getActiveEvents']);
        });
    });
});