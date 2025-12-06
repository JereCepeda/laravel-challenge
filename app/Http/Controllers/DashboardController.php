<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Dashboard principal - Carga layout con sidebar dinámico
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('role');
        
        $permissions = $user->role->permissions ?? [];
        $role = $user->role->slug;
        
        // Vista inicial según rol
        $initialView = match($role) {
            'admin' => 'dashboard.admin.statistics',
            'checker' => 'dashboard.checker.scan-menu',
            default => 'dashboard.home'
        };
        
        return view('layouts.dashboard', compact('user', 'permissions', 'role', 'initialView'));
    }
    
    /**
     * Carga secciones del dashboard vía AJAX (SPA)
     */
    public function loadSection(Request $request, string $section)
    {
        $user = $request->user();
        $user->load('role');
        
        $role = $user->role->slug;
        $permissions = $user->role->permissions ?? [];
        
        // Mapeo de secciones permitidas por rol
        $allowedSections = $this->getAllowedSections($role, $permissions);
        
        if (!in_array($section, $allowedSections)) {
            Log::info("Acceso denegado a la sección '{$section}' para el usuario ID {$user->id} con rol '{$role}'");
            return response()->json([
                'error' => 'No tienes permisos para acceder a esta sección'
            ], 403);
        }
        
        // Construir path de vista
        $viewPath = "dashboard.{$role}.{$section}";
        
        if (!view()->exists($viewPath)) {
            return response()->json([
                'error' => 'Sección no encontrada'
            ], 404);
        }
        
        return view($viewPath, compact('user', 'permissions'));
    }
    
    /**
     * Determina secciones permitidas según rol y permisos
     */
    private function getAllowedSections(string $role, array $permissions): array
    {
        $sections = [
            'admin' => [
                'statistics',
                'reports',
                'used-tickets',
                'redemptions-history'
            ],
            'checker' => [
                'scan-menu',
                'redeem-invitation',
                'validate-ticket'
            ]
        ];
        
        return $sections[$role] ?? [];
    }
}