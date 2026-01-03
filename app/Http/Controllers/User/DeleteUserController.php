<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DeleteUserController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $command = new DeleteUserCommand(Auth::user(), (int) $id);
            $this->commandBus->dispatch($command);

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (UserNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('users.index')
                ->with('error', 'Error al eliminar el usuario');
        }
    }
}
