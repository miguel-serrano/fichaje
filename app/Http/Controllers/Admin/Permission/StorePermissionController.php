<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Command\CreatePermissionCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use Illuminate\Http\RedirectResponse;

class StorePermissionController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(StorePermissionRequest $request): RedirectResponse
    {
        try {
            $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $command = new CreatePermissionCommand(
                $authenticatedUser->id()->value(),
                $request->validated('name'),
                $request->validated('slug'),
                $request->validated('bounded_context'),
                $request->validated('description')
            );

            $this->commandBus->dispatch($command);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permiso creado correctamente');
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el permiso');
        }
    }
}
