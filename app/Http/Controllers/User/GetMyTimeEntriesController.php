<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GetMyTimeEntriesController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(): View
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery);

        $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($authenticatedUser->id()->getValue());
        $registrosData = $this->queryBus->dispatch($dailyRegistrosQuery);

        return view('users.detail', [
            'user' => $authenticatedUser,
            'allRegistros' => $authenticatedUser->registrosHorarios(),
            'dailyRegistros' => $registrosData['registros'],
            'totalMes' => $registrosData['total_mes_actual'],
            'isAdmin' => false,
        ]);
    }
}
