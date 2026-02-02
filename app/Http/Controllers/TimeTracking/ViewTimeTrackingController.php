<?php

declare(strict_types=1);

namespace App\Http\Controllers\TimeTracking;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ViewTimeTrackingController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function __invoke(): View|string
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $userUuid = $user->uuid()->value();

            $secondsResponse = $this->queryBus->dispatch(GetAccumulatedSecondsQuery::create($userUuid));

            $checkOpenRegistry = $this->queryBus->dispatch(HasOpenTimeEntryQuery::create($userUuid));

            return view('registro_horario', [
                'user' => $user,
                'segundos' => $secondsResponse->seconds(),
                'tieneRegistroAbierto' => $checkOpenRegistry->hasOpenEntry(),
                'canClockIn' => $this->authorizationService->isGranted(TimeTrackingPermission::ClockIn->value, $user->id()->value()),
            ]);
        } catch (\Throwable $th) {
            return 'Error al cargar la página de registro horario: '.$th->getMessage();
        }
    }
}
