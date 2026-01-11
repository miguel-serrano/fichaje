<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Authorization\Application\Query\GetRoleByIdQuery;
use App\DDD\Authorization\Domain\Exceptions\RoleNotFoundException;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EditRoleController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {
    }

    public function __invoke(string $id): View|RedirectResponse
    {
        try {
            $role = $this->queryBus->dispatch(new GetRoleByIdQuery((int) $id));

            return view('admin.roles.edit', [
                'role' => $role,
            ]);
        } catch (RoleNotFoundException $e) {
            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        }
    }
}
