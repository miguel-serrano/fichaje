<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Authorization\Application\Command\DeleteRoleCommand;
use App\DDD\Authorization\Domain\Exceptions\CannotDeleteSystemRoleException;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DeleteRoleController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $command = new DeleteRoleCommand(
                Auth::id(),
                (int) $id
            );

            $this->commandBus->dispatch($command);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rol eliminado correctamente');
        } catch (CannotDeleteSystemRoleException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (RoleNotFoundException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('admin.roles.index')
                ->with('error', 'Error al eliminar el rol');
        }
    }
}
