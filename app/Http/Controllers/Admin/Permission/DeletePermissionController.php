<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Command\DeletePermissionCommand;
use App\DDD\Authorization\Domain\Exceptions\CannotDeleteSystemPermissionException;
use App\DDD\Authorization\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class DeletePermissionController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $command = new DeletePermissionCommand(
                $authenticatedUser->id()->value(),
                (int) $id
            );

            $this->commandBus->dispatch($command);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permiso eliminado correctamente');
        } catch (CannotDeleteSystemPermissionException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        } catch (PermissionNotFoundException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('admin.permissions.index')
                ->with('error', 'Error al eliminar el permiso');
        }
    }
}
