<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Application\Query\GetMonthlyTimeEntriesQuery;
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
            $dailyRegistrosData = $this->getDailyRegistros($userId);

            return view('users.detail', [
                'user' => $authenticatedUser,
                'allRegistros' => $this->getTodayRegistros($userId),
                'monthlyRegistros' => $this->getMonthlyRegistros($userId),
                'dailyRegistros' => $dailyRegistrosData['registros'],
                'totalMes' => $dailyRegistrosData['total_mes_actual'],
                'isAdmin' => false,
            ]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('bienvenido')
                ->with('error', 'Error al cargar tus registros de tiempo');
        }
    }

    /**
     * @return array{registros: array<mixed>, total_mes_actual: float}
     */
    private function getDailyRegistros(string $userId): array
    {
        return $this->queryBus->dispatch(
            GetUserDailyRegistrosQuery::create(
                authenticatedUserId: $userId,
                targetUserId: $userId,
            )
        )->response();
    }

    /**
     * @return array<mixed>
     */
    private function getTodayRegistros(string $userId): array
    {
        return $this->queryBus->dispatch(
            GetUserTodayRegistrosQuery::create(
                authenticatedUserId: $userId,
                targetUserId: $userId,
            )
        )->response();
    }

    /**
     * @return array<TimeEntry>
     */
    private function getMonthlyRegistros(int $userId): array
    {
        $now = new \DateTimeImmutable();

        return $this->queryBus->dispatch(
            GetMonthlyTimeEntriesQuery::create($userId, (int) $now->format('Y'), (int) $now->format('n'))
        )->timeEntries();
    }
}
