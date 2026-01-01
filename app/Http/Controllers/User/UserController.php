<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
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
        $users = $this->queryBus->dispatch(new GetAllUsersWithTimeQuery);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($users);
        }

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        try {
            $command = new CreateUserCommand($validated['email'], $validated['name']);
            $this->commandBus->dispatch($command);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        } catch (UserAlreadyExistsException $e) {
            return back()
                ->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        } catch (MaxUsersLimitExceededException $e) {
            return back()
                ->withInput()
                ->withErrors(['name' => $e->getMessage()]);
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
                return redirect()->route('users.index')->with('error', $e->getMessage());
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
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        try {
            $command = new DeleteUserCommand($id);
            $this->commandBus->dispatch($command);

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                $statusCode = $e instanceof \App\DDD\User\Domain\exceptions\UserNotFoundException ? 404 : 500;

                return response()->json(['error' => $e->getMessage()], $statusCode);
            }

            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
