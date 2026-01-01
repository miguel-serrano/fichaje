<?php

namespace App\Http\Controllers\User;

use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Application\Handler\CreateUserCommandHandler;
use App\DDD\User\Application\Handler\DeleteUserCommandHandler;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\DDD\User\Application\Command\GetUserDailyRegistrosQuery;
use App\DDD\User\Application\Handler\GetUserDailyRegistrosQueryHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Joselfonseca\LaravelTactician\CommandBusInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException; // Import UserNotFoundException
use App\DDD\User\Domain\Entity\User; // Import User entity

class UserController extends Controller
{
    public function __construct(
        private CreateUserCommandHandler $createUserHandler,
        private GetAllUsersWithTimeQueryHandler $getAllUsersWithTimeHandler,
        private DeleteUserCommandHandler $deleteUserHandler,
        private GetUserDailyRegistrosQueryHandler $getUserDailyRegistrosHandler,
        private CommandBusInterface $querybus
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $users = $this->getAllUsersWithTimeHandler->handle(new GetAllUsersWithTimeQuery());

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($users);
        }
        
        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255'
        ]);
        
        try {
            // Limitar máximo 10 usuarios
            $userCount = app(\App\Models\User::class)->count();
            if ($userCount >= 10) {
                $errorMsg = 'No es posible crear más de 10 usuarios.';
                if ($request->wantsJson() || $request->expectsJson()) {
                    return response()->json(['error' => $errorMsg], 422);
                }
                return back()
                    ->withInput()
                    ->withErrors(['name' => $errorMsg]);
            }
            $command = new CreateUserCommand($validated['email'], $validated['name']);
            $user = $this->createUserHandler->handle($command);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        } catch (\InvalidArgumentException $e) {

            return back()
                ->withInput()
                ->withErrors(['email' => $e->getMessage()]);
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
                $user = $this->querybus->dispatch($query);
            } catch (UserNotFoundException $e) {
                return redirect()->route('users.index')->with('error', $e->getMessage());
            }

            $dailyRegistrosQuery = new GetUserDailyRegistrosQuery($user->id()->getValue()); // Use integer ID
            $registrosData = $this->getUserDailyRegistrosHandler->handle($dailyRegistrosQuery);

            return view('users.show', [
                'user' => $user, // Pass the User entity object
                'allRegistros' => $user->registrosHorarios(), // Pass all time entries
                'dailyRegistros' => $registrosData['registros'],
                'totalMes' => $registrosData['total_mes_actual']
            ]);
        } catch (\Exception $e) { // Catch other potential exceptions
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }
    }


    public function destroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        try {
            $command = new DeleteUserCommand($id);
            $this->deleteUserHandler->handle($command);

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                $statusCode = $e instanceof \App\DDD\User\Domain\exceptions\UserNotFoundException ? 404 : 500;
                return response()->json(['error' => $e->getMessage()], $statusCode);
            }
            

        }
    }
}