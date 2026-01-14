<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Query\GetAllUsersQuery;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ListUsersController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): View|RedirectResponse
    {
        try {
            $users = $this->queryBus->dispatch(
                GetAllUsersQuery::create(authenticatedUserId: Auth::id())
            )->response();

            return view('users.index', [
                'users' => $users,
                'isAdmin' => true,
            ]);
        } catch (UnauthorizedException $e) {
            return redirect()->route('bienvenido')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('bienvenido')
                ->with('error', 'Error al cargar la lista de usuarios');
        }
    }
}
