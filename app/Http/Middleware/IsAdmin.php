<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check by role only (is_admin field removed)
        $isAdmin = $user->hasRole('super_admin')
            || $user->hasRole('admin');

        if (!$isAdmin) {
            abort(403, 'No tienes permisos para acceder a esta pagina.');
        }

        return $next($request);
    }
}
