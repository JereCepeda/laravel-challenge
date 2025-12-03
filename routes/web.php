<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'index'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('role');
        $permissions = $user->role->permissions ?? [];
        return view('dashboard.index', compact('user', 'permissions'));
    })->name('dashboard');
    
    // Simple logout route
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('auth.login')->with('success', 'Logout successful');
    })->name('logout');
});