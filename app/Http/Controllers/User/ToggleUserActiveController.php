<?php

namespace App\Http\Controllers\User;

use App\DDD\User\Application\Services\UserAuthorizationService;
use App\Http\Controllers\Controller;
use App\Models\User as EloquentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ToggleUserActiveController extends Controller
{
    public function __construct(private readonly UserAuthorizationService $authorizationService)
    {
    }

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $this->authorizationService->ensureCanToggleActive(Auth::user());

            $targetUser = EloquentUser::query()->findOrFail($id);
            $targetUser->is_active = ! $targetUser->is_active;
            $targetUser->save();

            $message = $targetUser->is_active ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';

            return redirect()->route('users.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error al cambiar el estado del usuario: '.$e->getMessage());
        }
    }
}
