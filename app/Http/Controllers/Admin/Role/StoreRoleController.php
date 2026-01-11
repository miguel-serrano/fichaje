<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Authorization\Application\Command\CreateRoleCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StoreRoleController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    public function __invoke(StoreRoleRequest $request): RedirectResponse
    {
        try {
            $command = new CreateRoleCommand(
                Auth::id(),
                $request->validated('name'),
                $request->validated('slug'),
                $request->validated('description'),
                (int) $request->validated('hierarchy', 0)
            );

            $role = $this->commandBus->dispatch($command);

            return redirect()->route('admin.roles.show', ['id' => $role['id']])
                ->with('success', 'Rol creado correctamente');
        } catch (UnauthorizedException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el rol');
        }
    }
}
