<?php

namespace App\Http\Controllers\User;

use App\DDD\Authorization\Application\Query\GetAllRolesQuery;
use App\DDD\Authorization\Application\Query\GetUserRolesQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShowUserController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(string $id): View|RedirectResponse
    {
        try {
            $query = new GetUserByIdQuery(Auth::id(), (int) $id);
            /** @var User $user */
            $user = $this->queryBus->dispatch($query);

            $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($user->id()->value());
            $registrosData = $this->queryBus->dispatch($dailyRegistrosQuery);

            $allRolesQuery = new GetAllRolesQuery();
            $allRoles = $this->queryBus->dispatch($allRolesQuery);

            $userRolesQuery = new GetUserRolesQuery($user->id()->value());
            $userRoles = $this->queryBus->dispatch($userRolesQuery);

            return view('users.show', [
                'user' => $user,
                'allRegistros' => $user->timeEntries(),
                'dailyRegistros' => $registrosData['registros'],
                'totalMes' => $registrosData['total_mes_actual'],
                'allRoles' => $allRoles,
                'userRoles' => $userRoles,
            ]);
        } catch (UserNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('users.index')
                ->with('error', 'Error al cargar el usuario');
        }
    }
}
