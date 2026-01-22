<?php

namespace App\Http\Controllers\TimeTracking;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\Http\Controllers\Controller;
use App\Models\User as EloquentUser;
use Illuminate\Http\RedirectResponse;

class ClockOutController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function __invoke(): RedirectResponse
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $eloquentUser = EloquentUser::query()->where('uuid', $user->uuid()->value())->first();
            if (!$eloquentUser || !$eloquentUser->is_active) {
                return redirect()->route('user.me')
                    ->with('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');
            }

            if (!$this->permissionChecker->hasPermission($user, TimeTrackingPermission::ClockOut->value)) {
                return redirect()->route('user.me')
                    ->with('error', 'No tienes permisos para fichar salida.');
            }

            $this->commandBus->dispatch(
                ClockOutCommand::create($user->uuid()->value())
            );

            return redirect()->route('user.me')
                ->with('success', 'Salida registrada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('user.me')
                ->with('error', $e->getMessage());
        }
    }
}
