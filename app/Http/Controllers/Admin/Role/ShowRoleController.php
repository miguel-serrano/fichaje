<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Administration\Application\Query\GetAllPermissionsQuery;
use App\DDD\Administration\Application\Query\GetRoleByIdQuery;
use App\DDD\Administration\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowRoleController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(string $id): View|RedirectResponse
    {
        try {
            $role = $this->queryBus->dispatch(new GetRoleByIdQuery((int) $id));
            $allPermissions = $this->queryBus->dispatch(new GetAllPermissionsQuery());

            $permissionsByContext = collect($allPermissions)->groupBy('bounded_context')->toArray();

            $rolePermissionIds = array_map(
                fn ($p) => $p['id'],
                $role['permissions']
            );

            return view('admin.roles.show', [
                'role' => $role,
                'permissionsByContext' => $permissionsByContext,
                'rolePermissionIds' => $rolePermissionIds,
            ]);
        } catch (RoleNotFoundException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        }
    }
}
