<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('role');
        
        $permissions = $user->role->permissions ?? [];
        
        return view('dashboard.index', compact('user', 'permissions'));
    }

    public function statistics()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('role');
        $permissions = $user->role->permissions ?? [];
        
        // Verificar permisos
        if (!in_array('view_statistics', $permissions)) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }
        
        return view('dashboard.statistics', compact('user', 'permissions'));
    }
}