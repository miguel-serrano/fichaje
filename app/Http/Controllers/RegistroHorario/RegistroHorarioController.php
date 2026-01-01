<?php

namespace App\Http\Controllers\RegistroHorario;

use Illuminate\Http\Request;
use App\DDD\TimeTracking\Application\Command\ClockInCommand;
use App\DDD\TimeTracking\Application\Command\ClockOutCommand;
use App\DDD\TimeTracking\Application\Query\GetAccumulatedSecondsQuery;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;

class RegistroHorarioController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private UserRepositoryInterface $userRepository
    ) {}

    public function ficharEntrada(Request $request)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $command = new ClockInCommand($validated['userUuid']);
            $this->commandBus->dispatch($command);
            
            // Get User ID for redirection
            $user = $this->userRepository->findByUuid(new Uuid($validated['userUuid']));
            if (!$user) {
                // This case should ideally not happen if handle() succeeded, but for safety
                throw new UserNotFoundException('User not found after clock-in for redirection.');
            }

            return redirect()->route('users.show', ['id' => $user->id()->getValue()]) // Changed redirect
                ->with('success', 'Entrada registrada correctamente');
        } catch (\Exception $e) {
            // If user not found during redirection or other error
            return redirect()->route('users.index') // Fallback redirect
                ->with('error', $e->getMessage());
        }
    }

    public function ficharSalida(Request $request, ?int $registroHorarioId = null)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $command = new ClockOutCommand($validated['userUuid'], $registroHorarioId);
            $this->commandBus->dispatch($command);
            
            $successMessage = $registroHorarioId !== null 
                ? 'Fichaje cerrado correctamente' 
                : 'Salida registrada correctamente';

            // Get User ID for redirection
            $user = $this->userRepository->findByUuid(new Uuid($validated['userUuid']));
            if (!$user) {
                // This case should ideally not happen if handle() succeeded, but for safety
                throw new UserNotFoundException('User not found after clock-out for redirection.');
            }
            
            return redirect()->route('users.show', ['id' => $user->id()->getValue()])
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            // Try to redirect to user's show page if userUuid is available
            try {
                $user = $this->userRepository->findByUuid(new Uuid($validated['userUuid']));
                if ($user) {
                    return redirect()->route('users.show', ['id' => $user->id()->getValue()])
                        ->with('error', $e->getMessage());
                }
            } catch (\Exception $subE) {
                // Fall through to index redirect
            }
            
            // Fallback redirect to users index
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $users = $this->queryBus->dispatch(new GetAllUsersWithTimeQuery());
        $userUuid = $request->input('userUuid');
        $segundos = 0;
        $tieneRegistroAbierto = false;

        if ($userUuid) {
            $segundos = $this->queryBus->dispatch(new GetAccumulatedSecondsQuery($userUuid));
            $tieneRegistroAbierto = $this->queryBus->dispatch(new HasOpenTimeEntryQuery($userUuid));
        }

        return view('registro_horario', [
            'users' => $users,
            'segundos' => $segundos,
            'selectedUserUuid' => $userUuid,
            'tieneRegistroAbierto' => $tieneRegistroAbierto
        ]);
    }

}
