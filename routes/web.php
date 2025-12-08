<?php

use Illuminate\Support\Facades\Route;

// Página de login (pública - solo vista)
Route::get('/login', function() {
    return view('auth.login');
})->name('login');

// Dashboard loader (pública - valida token en frontend)
Route::get('/dashboard', function() {
    return view('dashboard-loader');
})->name('dashboard');

// Raíz redirige al login
Route::get('/', function() {
    return redirect()->route('login');
});