<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->remember_token !== 'soyAdm1n') {
            abort(403, 'No tienes permisos para acceder a esta página.');
        }

        return $next($request);
    }
}
