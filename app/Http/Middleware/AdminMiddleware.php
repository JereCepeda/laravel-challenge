<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {

        if (!$request->user()) {
            return response()->json([
                'error' => 'Authentication required',
                'message' => 'You must be logged in to access this resource'
            ], 401);
        }

        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Admin privileges required to access this resource'
            ], 403);
        }

        return $next($request);
    }
}
