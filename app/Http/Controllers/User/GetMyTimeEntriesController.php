<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Domain\TimeEntry;
use App\DDD\User\Application\Query\GetUserDailyRegistrosQuery;
use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GetMyTimeEntriesController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(): View|RedirectResponse
    {
        try {
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery);

            $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($authenticatedUser->id()->getValue());
            $registrosData = $this->queryBus->dispatch($dailyRegistrosQuery);

            $todayRegistrosQuery = new GetUserTodayRegistrosQuery($authenticatedUser->id()->getValue());
            $todayRegistrosData = $this->queryBus->dispatch($todayRegistrosQuery);

            $todayRegistros = array_map(
                fn (array $r) => TimeEntry::fromPrimitives($r['id'], $r['user_id'], $r['entrada'], $r['salida']),
                $todayRegistrosData
            );

            $monthlyRegistros = array_filter(
                $authenticatedUser->registrosHorarios(),
                fn (TimeEntry $r) => $r->entrada()->format('Y-m') === date('Y-m')
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
