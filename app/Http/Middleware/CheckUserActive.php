<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()->is_active) {
            return redirect()->route('bienvenido')
                ->with('error', 'Tu cuenta está pendiente de activación. Contacta con un administrador.');
        }

        return $next($request);
    }
}
