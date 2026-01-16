<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GetMyTimeEntriesController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): View|RedirectResponse
    {
        try {
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());
            $userId = $authenticatedUser->id()->value();

            $registrosData = $this->queryBus->dispatch(
                GetUserDailyRegistrosQuery::create(
                    authenticatedUserId: $userId,
                    targetUserId: $userId,
                )
            )->response();

            $todayRegistros = $this->queryBus->dispatch(
                GetUserTodayRegistrosQuery::create(
                    authenticatedUserId: $userId,
                    targetUserId: $userId,
                )
            )->response();

            $monthlyRegistros = array_filter(
                $authenticatedUser->timeEntries(),
                fn (TimeEntry $r) => $r->startTime()->format('Y-m') === date('Y-m')
            );

            return view('users.detail', [
                'user' => $authenticatedUser,
                'allRegistros' => $todayRegistros,
                'monthlyRegistros' => $monthlyRegistros,
                'dailyRegistros' => $registrosData['registros'],
                'totalMes' => $registrosData['total_mes_actual'],
                'isAdmin' => false,
            ]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('bienvenido')
                ->with('error', 'Error al cargar tus registros de tiempo');
        }
    }
}
