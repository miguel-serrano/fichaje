<?php

namespace App\Http\Controllers\TimeTracking;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\User\Domain\Exceptions\UserNotActiveException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ClockOutController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): RedirectResponse
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $this->commandBus->dispatch(
                ClockOutCommand::create($user->uuid()->value())
            );

            return redirect()->route('user.me')
                ->with('success', 'Salida registrada correctamente');
        } catch (UserNotActiveException) {
            return redirect()->route('bienvenido')
                ->with('error', 'Tu cuenta está pendiente de activación, en breve se activará.');
        } catch (\Exception $e) {
            return redirect()->route('user.me')
                ->with('error', $e->getMessage());
        }
    }
}
