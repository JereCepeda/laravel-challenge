<?php

namespace App\Services\Dashboard\Contracts;

use App\Models\User;

interface RoleDashboardInterface
{
    /**
     * Obtener la vista inicial del rol
     */
    public function getInitialView(): string;
    
    /**
     * Obtener secciones permitidas para el rol
     */
    public function getAllowedSections(array $permissions): array;
    
    /**
     * Obtener datos específicos para una sección
     */
    public function getDataForSection(string $section, User $user): array;
}