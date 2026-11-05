<?php

namespace App\Http\Middleware;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use HttpStatusCode\HttpStatus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige que el usuario autenticado tenga todos los permisos indicados.
 *
 * La decisión la toman los voters (AuthorizationService), igual que en los handlers,
 * para que ruta, handler y menú apliquen exactamente las mismas reglas.
 *
 * Uso: ->middleware('permission:user.view') o 'permission:holiday.approve,holiday.reject'
 */
class HasPermission
{
    public function __construct(
        private readonly AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(Request $request, \Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (null === $user) {
            return redirect()->route('login');
        }

        foreach ($permissions as $permission) {
            if (!$this->authorizationService->isGranted($permission, (int) $user->id)) {
                abort(HttpStatus::FORBIDDEN, 'No tienes permisos para acceder a esta página.');
            }
        }

        return $next($request);
    }
}
