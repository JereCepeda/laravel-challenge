<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\InvitationRedemption;

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
        
        $allowedSections = $this->getAllowedSections($role, $permissions);
        
        if (!in_array($section, $allowedSections)) {
            Log::info("Acceso denegado a la sección '{$section}' para el usuario ID {$user->id} con rol '{$role}'");
            return response()->json([
                'error' => 'No tienes permisos para acceder a esta sección'
            ], 403);
        }
        
        $viewPath = "dashboard.{$role}.{$section}";
        
        if (!view()->exists($viewPath)) {
            return response()->json([
                'error' => 'Sección no encontrada'
            ], 404);
        }
        $data = $this->getDataForSection($section);
        $data['user'] = $user;
        $data['permissions'] = $permissions;
        
        return view($viewPath, $data);
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
    
    /**
     * Obtener datos adicionales específicos para cada sección
     */
    private function getDataForSection(string $section): array
    {
        return match($section) {
        
            'used-tickets' => [
                'events' => Ticket::select('event_name', 'event_date', 'sector')
                    ->groupBy('event_name', 'event_date', 'sector')
                    ->orderBy('event_date', 'desc')
                    ->get(),
                    
                'filters' => Ticket::select('event_name', 'event_date')
                    ->distinct()
                    ->groupBy('event_name', 'event_date')
                    ->orderBy('event_name')
                    ->get()
            ],
            'redemptions-history' => [
                'events' => InvitationRedemption::select('event_name', 'sector', 'event_date', 'tickets_generated', 'invitation_id','guest_count','user_agent','redeemed_at')
                    
                    ->orderBy('event_name')
                    ->get(),
                'filters' => InvitationRedemption::select('event_name', 'event_date', 'sector')
                    ->distinct()
                    ->groupBy('event_name', 'event_date', 'sector')
                    ->orderBy('event_name')
                    ->get()
            ],
            default => []
        };
    }
}