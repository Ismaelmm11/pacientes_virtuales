<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Role;

/**
 * Middleware: Solo profesores y admins pueden acceder.
 * Si el usuario es alumno, redirige al dashboard de alumno.
 */
class EnsureIsTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Admin también puede acceder a rutas de profesor
        if ($user && ($user->isTeacher() || $user->isAdmin())) {
            return $next($request);
        }

        return redirect()->route('student.dashboard')
            ->with('error', 'No tienes permiso para acceder a esa sección.');
    }
}