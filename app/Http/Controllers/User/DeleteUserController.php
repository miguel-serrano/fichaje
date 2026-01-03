<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Exceptions\CannotDeleteAdminUserException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class DeleteUserController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $command = new DeleteUserCommand($id);
            $this->commandBus->dispatch($command);

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (CannotDeleteAdminUserException $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        } catch (UserNotFoundException $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }
    }
}
