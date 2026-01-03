<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User as EloquentUser;
use Illuminate\Http\RedirectResponse;

class ToggleUserActiveController extends Controller
{
    public function __invoke(string $id): RedirectResponse
    {
        try {
            $user = EloquentUser::query()->findOrFail($id);

            $user->is_active = ! $user->is_active;
            $user->save();

            $message = $user->is_active ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';

            return redirect()->route('users.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error al cambiar el estado del usuario: '.$e->getMessage());
        }
    }
}
