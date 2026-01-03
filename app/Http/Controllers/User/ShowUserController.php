<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowUserController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request, string $id): View|JsonResponse|RedirectResponse
    {
        try {
            $query = new GetUserByIdQuery($id);

            try {
                /** @var User $user */
                $user = $this->queryBus->dispatch($query);
            } catch (UserNotFoundException $e) {
                return redirect()->route('users.index')->with('error', $e->getMessage());
            }

            $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($user->id()->getValue());
            $registrosData = $this->queryBus->dispatch($dailyRegistrosQuery);

            return view('users.show', [
                'user' => $user,
                'allRegistros' => $user->registrosHorarios(),
                'dailyRegistros' => $registrosData['registros'],
                'totalMes' => $registrosData['total_mes_actual'],
            ]);
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }
    }
}
