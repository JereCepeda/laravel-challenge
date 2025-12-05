<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;

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
});