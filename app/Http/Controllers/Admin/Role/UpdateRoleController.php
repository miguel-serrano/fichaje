<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Command\UpdateRoleCommand;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;

class UpdateRoleController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(UpdateRoleRequest $request, string $id): RedirectResponse
    {
        try {
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $command = new UpdateRoleCommand(
                $authenticatedUser->id()->value(),
                (int) $id,
                $request->validated('name'),
                $request->validated('description'),
                (int) $request->validated('hierarchy', 0)
            );

            $this->commandBus->dispatch($command);

            return redirect()->route('admin.roles.show', ['id' => $id])
                ->with('success', 'Rol actualizado correctamente');
        } catch (RoleNotFoundException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el rol');
        }
    }
}
