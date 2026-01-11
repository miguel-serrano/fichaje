<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Authorization\Application\Command\DeletePermissionCommand;
use App\DDD\Authorization\Domain\Exceptions\CannotDeleteSystemPermissionException;
use App\DDD\Authorization\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DeletePermissionController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $command = new DeletePermissionCommand(
                Auth::id(),
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
