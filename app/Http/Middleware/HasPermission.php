<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                abort(403, "No tienes el permiso necesario: {$permission}");
            }
        }

        return $next($request);
    }
}
