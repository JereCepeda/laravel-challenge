<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Valida que el usuario tenga uno de los roles permitidos
     * 
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles Roles permitidos (ej: 'admin', 'checker')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Authentication required'
            ], 401);
        }

        // Cargar la relación role si no está cargada
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        $userRole = $user->role->slug;

        // Verificar si el usuario tiene alguno de los roles permitidos
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'error' => 'Unauthorized access'
            ], 403);
        }

        return $next($request);
    }
}
