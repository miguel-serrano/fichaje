<?php

namespace App\Http\Controllers\RegistroHorario;

use Illuminate\Http\Request;
use App\DDD\RegistroHorario\Application\FicharEntrada;
use App\DDD\RegistroHorario\Application\FicharSalida;
use App\DDD\RegistroHorario\Application\ObtenerSegundosAcumulados;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\Http\Controllers\Controller;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\User\Domain\Interface\UserRepositoryInterface; // Import UserRepositoryInterface
use App\DDD\User\Domain\ValueObjects\Uuid; // Import Uuid Value Object
use App\DDD\User\Domain\exceptions\UserNotFoundException; // Import UserNotFoundException

class RegistroHorarioController extends Controller
{
    public function __construct(
        private FicharEntrada $entradaUC,
        private FicharSalida $salidaUC,
        private ObtenerSegundosAcumulados $segundosUC,
        private GetAllUsersWithTimeQueryHandler $getAllUsersWithTimeHandler,
        private RegistroHorarioService $registroHorarioService,
        private UserRepositoryInterface $userRepository // Inject UserRepositoryInterface
    ) {}

    public function ficharEntrada(Request $request)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $this->entradaUC->handle($validated['userUuid']);
            
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

    public function ficharSalida(Request $request)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $this->salidaUC->handle($validated['userUuid']);

            // Get User ID for redirection
            $user = $this->userRepository->findByUuid(new Uuid($validated['userUuid']));
            if (!$user) {
                // This case should ideally not happen if handle() succeeded, but for safety
                throw new UserNotFoundException('User not found after clock-out for redirection.');
            }
            
            return redirect()->route('users.show', ['id' => $user->id()->getValue()]) // Changed redirect
                ->with('success', 'Salida registrada correctamente');
        } catch (\Exception $e) {
            // If user not found during redirection or other error
            return redirect()->route('users.index') // Fallback redirect
                ->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $users = $this->getAllUsersWithTimeHandler->handle(new GetAllUsersWithTimeQuery());
        $userUuid = $request->input('userUuid');
        $segundos = 0;
        $tieneRegistroAbierto = false;

        if ($userUuid) {
            $segundos = $this->segundosUC->handle($userUuid);
            $tieneRegistroAbierto = $this->registroHorarioService->hasOpenRegistro($userUuid);
        }

        return view('registro_horario', [
            'users' => $users,
            'segundos' => $segundos,
            'selectedUserUuid' => $userUuid,
            'tieneRegistroAbierto' => $tieneRegistroAbierto
        ]);
    }

    public function cerrarRegistro(Request $request, int $registroHorarioId)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $this->registroHorarioService->cerrarRegistro($validated['userUuid'], $registroHorarioId);

            // Get User ID for redirection
            $user = $this->userRepository->findByUuid(new Uuid($validated['userUuid']));
            if (!$user) {
                throw new UserNotFoundException('User not found after closing registro for redirection.');
            }

            return redirect()->route('users.show', ['id' => $user->id()->getValue()])
                ->with('success', 'Fichaje cerrado correctamente');
        } catch (\Exception $e) {
            // Fallback redirect to the user's detail page or index
            $userUuid = $validated['userUuid'] ?? null;
            if ($userUuid) {
                // Try to redirect to user's show page if userUuid is available
                try {
                    $user = $this->userRepository->findByUuid(new Uuid($userUuid));
                    if ($user) {
                        return redirect()->route('users.show', ['id' => $user->id()->getValue()])
                            ->with('error', $e->getMessage());
                    }
                } catch (\Exception $subE) {
                    // Fall through to index redirect
                }
            }
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
