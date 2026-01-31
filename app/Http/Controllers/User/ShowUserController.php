<?php

namespace App\Http\Controllers\User;

use App\DDD\Administration\Application\Query\GetAllRolesQuery;
use App\DDD\Administration\Application\Query\GetUserRolesQuery;
use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            /** @var User $user */
            $user = $this->queryBus->dispatch(
                GetUserByIdQuery::create(
                    authenticatedUserId: $authenticatedUser->id()->value(),
                    targetUserId: (int) $id,
                )
            );

            $registrosData = $this->queryBus->dispatch(
                GetUserDailyRegistrosQuery::create(
                    authenticatedUserId: $authenticatedUser->id()->value(),
                    targetUserId: $user->id()->value(),
                )
            )->response();

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
