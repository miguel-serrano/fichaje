<?php

namespace App\Http\Controllers\RegistroHorario;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\Http\Controllers\Controller;
use App\Models\User as EloquentUser;

class RegistroHorarioController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus
    ) {}

    public function ficharEntrada()
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery);

            // Verificar que el usuario esté activo
            $eloquentUser = EloquentUser::query()->where('uuid', $user->uuid()->getValue())->first();
            if (! $eloquentUser || ! $eloquentUser->is_active) {
                return redirect()->route('user.me')
                    ->with('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');
            }

            $command = new ClockInCommand($user->uuid()->getValue());
            $this->commandBus->dispatch($command);

            return redirect()->route('user.me')
                ->with('success', 'Entrada registrada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('user.me')
                ->with('error', $e->getMessage());
        }
    }

    public function ficharSalida(?int $registroHorarioId = null)
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery);

            // Verificar que el usuario esté activo
            $eloquentUser = EloquentUser::query()->where('uuid', $user->uuid()->getValue())->first();
            if (! $eloquentUser || ! $eloquentUser->is_active) {
                return redirect()->route('user.me')
                    ->with('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');
            }

            $command = new ClockOutCommand($user->uuid()->getValue(), $registroHorarioId);
            $this->commandBus->dispatch($command);

            $successMessage = $registroHorarioId !== null
                ? 'Fichaje cerrado correctamente'
                : 'Salida registrada correctamente';

            return redirect()->route('user.me')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return redirect()->route('user.me')
                ->with('error', $e->getMessage());
        }
    }

    public function index()
    {
        $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery);
        $userUuid = $user->uuid()->getValue();

        $segundos = $this->queryBus->dispatch(new GetAccumulatedSecondsQuery($userUuid));
        $tieneRegistroAbierto = $this->queryBus->dispatch(new HasOpenTimeEntryQuery($userUuid));

        return view('registro_horario', [
            'user' => $user,
            'segundos' => $segundos,
            'tieneRegistroAbierto' => $tieneRegistroAbierto,
        ]);
    }
}
