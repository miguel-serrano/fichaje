<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Command\UpdatePermissionCommand;
use App\DDD\Authorization\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Illuminate\Http\RedirectResponse;

class UpdatePermissionController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(UpdatePermissionRequest $request, string $id): RedirectResponse
    {
        try {
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $command = new UpdatePermissionCommand(
                $authenticatedUser->id()->value(),
                (int) $id,
                $request->validated('name'),
                $request->validated('description')
            );

            $this->commandBus->dispatch($command);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permiso actualizado correctamente');
        } catch (PermissionNotFoundException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->with('error', 'Error al actualizar el permiso');
        }
    }
}
