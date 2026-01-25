<?php

namespace App\Http\Controllers\TimeTracking;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
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
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function __invoke(): View|string
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $userUuid = $user->uuid()->value();

            $canClockIn = $this->permissionChecker->hasPermission($user, TimeTrackingPermission::ClockIn->value);

            $secondsResponse = $this->queryBus->dispatch(GetAccumulatedSecondsQuery::create($userUuid));

            $checkOpenRegistry = $this->queryBus->dispatch(HasOpenTimeEntryQuery::create($userUuid));

            return view('registro_horario', [
                'user' => $user,
                'segundos' => $secondsResponse->seconds(),
                'tieneRegistroAbierto' => $checkOpenRegistry->hasOpenEntry(),
                'canClockIn' => $canClockIn,
            ]);
        } catch (\Throwable $th) {
            return 'Error al cargar la página de registro horario: '.$th->getMessage();
        }
    }
}
