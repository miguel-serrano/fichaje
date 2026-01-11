<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Authorization\Application\Command\SyncPermissionsToRoleCommand;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SyncPermissionsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SyncPermissionsController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(SyncPermissionsRequest $request, string $id): RedirectResponse
    {
        try {
            $permissionIds = array_map('intval', $request->validated('permissions', []));

            $command = new SyncPermissionsToRoleCommand(
                Auth::id(),
                (int) $id,
                $permissionIds
            );

            $this->commandBus->dispatch($command);

            return redirect()->route('admin.roles.show', ['id' => $id])
                ->with('success', 'Permisos actualizados correctamente');
        } catch (RoleNotFoundException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->with('error', 'Error al actualizar los permisos');
        }
    }
}
