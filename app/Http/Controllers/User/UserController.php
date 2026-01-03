<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\CannotDeleteAdminUserException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User as EloquentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        // Obtener el usuario autenticado
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery);

        // Obtener el modelo Eloquent para verificar el remember_token
        $eloquentUser = EloquentUser::query()->where('uuid', $authenticatedUser->uuid()->getValue())->first();

        $isAdmin = $eloquentUser->remember_token === 'soyAdm1n';

        if ($isAdmin) {
            // Si es admin, mostrar todos los usuarios en formato tabla
            $users = $this->queryBus->dispatch(new GetAllUsersWithTimeQuery);

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json($users);
            }

            return view('users.index', [
                'users' => $users,
                'isAdmin' => $isAdmin,
            ]);
        } else {
            // Si no es admin, mostrar vista detallada con sus fichajes
            $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($authenticatedUser->id()->getValue());
            $registrosData = $this->queryBus->dispatch($dailyRegistrosQuery);

            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'user' => $authenticatedUser,
                    'allRegistros' => $authenticatedUser->registrosHorarios(),
                    'dailyRegistros' => $registrosData['registros'],
                    'totalMes' => $registrosData['total_mes_actual'],
                ]);
            }

            return view('users.detail', [
                'user' => $authenticatedUser,
                'allRegistros' => $authenticatedUser->registrosHorarios(),
                'dailyRegistros' => $registrosData['registros'],
                'totalMes' => $registrosData['total_mes_actual'],
                'isAdmin' => $isAdmin,
            ]);
        }
    }

    public function show(Request $request, string $id): View|JsonResponse|RedirectResponse
    {
        try {
            $query = new GetUserByIdQuery($id);

            // Object queryResponse
            // Catch UserNotFoundException specifically
            try {
                /** @var User $user */
                $user = $this->queryBus->dispatch($query);
            } catch (UserNotFoundException $e) {
                return redirect()->route('user.index')->with('error', $e->getMessage());
            }

            $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($user->id()->getValue()); // Use integer ID
            $registrosData = $this->queryBus->dispatch($dailyRegistrosQuery);

            return view('users.show', [
                'user' => $user, // Pass the User entity object
                'allRegistros' => $user->registrosHorarios(), // Pass all time entries
                'dailyRegistros' => $registrosData['registros'],
                'totalMes' => $registrosData['total_mes_actual'],
            ]);
        } catch (\Exception $e) { // Catch other potential exceptions
            return redirect()->route('user.index')->with('error', $e->getMessage());
        }
    }

    public function toggleActive(string $id): RedirectResponse
    {
        try {
            // Obtener el usuario usando el modelo Eloquent
            $user = EloquentUser::query()->findOrFail($id);

            // Toggle el estado is_active
            $user->is_active = ! $user->is_active;
            $user->save();

            $message = $user->is_active
                ? 'Usuario activado correctamente'
                : 'Usuario desactivado correctamente';

            return redirect()->route('user.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Error al cambiar el estado del usuario: '.$e->getMessage());
        }
    }

    public function delete(Request $request, string $id): RedirectResponse|JsonResponse
    {
        try {
            $command = new DeleteUserCommand($id);
            $this->commandBus->dispatch($command);

            return redirect()->route('user.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (CannotDeleteAdminUserException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 403);
            }

            return redirect()->route('user.index')
                ->with('error', $e->getMessage());
        } catch (UserNotFoundException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 404);
            }

            return redirect()->route('user.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->route('user.index')
                ->with('error', $e->getMessage());
        }
    }
}
