<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware: Solo alumnos pueden acceder.
 * Usado para proteger rutas que no tienen sentido para profesores/admin
 * (ej: hacer una simulación como alumno).
 */
class EnsureIsStudent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isStudent()) {
            return $next($request);
        }

        $route = $user?->isAdmin()
            ? 'admin.dashboard'
            : 'teacher.dashboard';

        return redirect()->route($route)
            ->with('error', 'Esta sección es solo para alumnos.');
    }
}