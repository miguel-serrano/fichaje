<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Command\RemoveRoleFromUserCommand;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RemoveRoleFromUserController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(string $id, string $roleSlug): RedirectResponse
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $command = new RemoveRoleFromUserCommand(
                $user->id()->value(),
                (int) $id,
                $roleSlug
            );
            $this->commandBus->dispatch($command);

            return redirect()->route('user.show', ['id' => $id])
                ->with('success', 'Rol eliminado correctamente');
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
                ->with('error', 'Error al eliminar el rol');
        }
    }
}
