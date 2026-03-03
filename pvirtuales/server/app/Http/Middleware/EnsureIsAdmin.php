<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware: Solo admins pueden acceder.
 * Cualquier otro rol redirige a su dashboard correspondiente.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Redirige al dashboard del rol que tiene
        $route = $user?->isTeacher()
            ? 'teacher.dashboard'
            : 'student.dashboard';

        return redirect()->route($route)
            ->with('error', 'No tienes permiso para acceder a esa sección.');
    }
}