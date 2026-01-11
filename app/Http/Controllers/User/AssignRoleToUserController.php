<?php

namespace App\Http\Controllers\User;

use App\DDD\Authorization\Application\Command\AssignRoleToUserCommand;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AssignRoleToUserController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(AssignRoleRequest $request, string $id): RedirectResponse
    {
        try {
            $command = new AssignRoleToUserCommand(
                Auth::id(),
                (int) $id,
                $request->validated('role_slug')
            );
            $this->commandBus->dispatch($command);

            return redirect()->route('user.show', ['id' => $id])
                ->with('success', 'Rol asignado correctamente');
        } catch (UserNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (RoleNotFoundException $e) {
            return redirect()->route('user.show', ['id' => $id])
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('user.show', ['id' => $id])
                ->with('error', 'Error al asignar el rol');
        }
    }
}
