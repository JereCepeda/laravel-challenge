<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Services\Dashboard\Strategies\DashboardStrategyFactory;

class DashboardService
{
    public function __construct(
        private DashboardStrategyFactory $strategyFactory
    ) {}
    
    /**
     * Obtener datos iniciales del dashboard
     */
    public function getDashboardData(User $user): array
    {
        $role = $user->role->slug;
        $permissions = $user->role->permissions ?? [];
        
        $strategy = $this->strategyFactory->make($role);
        
        return [
            'user' => $user,
            'role' => $role,
            'permissions' => $permissions,
            'initialView' => $strategy->getInitialView(),
            'allowedSections' => $strategy->getAllowedSections($permissions)
        ];
    }
    
    /**
     * Cargar datos de una sección específica
     */
    public function loadSection(User $user, string $section): array
    {
        $role = $user->role->slug;
        $permissions = $user->role->permissions ?? [];
        
        $strategy = $this->strategyFactory->make($role);
                
        // Obtener datos
        $data = $strategy->getDataForSection($section, $user);
        $data['user'] = $user;
        $data['permissions'] = $permissions;
        
        return $data;
    }
    
    /**
     * Obtener la ruta de la vista para una sección
     */
    public function getViewPath(string $role, string $section): string
    {
        return "dashboard.{$role}.{$section}";
    }
}