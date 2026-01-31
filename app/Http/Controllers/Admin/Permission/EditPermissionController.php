<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Administration\Application\Query\GetPermissionByIdQuery;
use App\DDD\Administration\Domain\Exceptions\PermissionNotFoundException;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EditPermissionController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(string $id): View|RedirectResponse
    {
        try {
            $permission = $this->queryBus->dispatch(new GetPermissionByIdQuery((int) $id));

            if ($permission['is_system']) {
                return redirect()->route('admin.permissions.index')
                    ->with('error', 'Los permisos del sistema no se pueden editar.');
            }

            $contexts = array_column(BoundedContext::cases(), 'value');

            return view('admin.permissions.edit', [
                'permission' => $permission,
                'contexts' => $contexts,
            ]);
        } catch (PermissionNotFoundException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', $e->getMessage());
        }
    }
}
