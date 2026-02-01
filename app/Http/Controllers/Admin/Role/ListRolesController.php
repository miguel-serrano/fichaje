<?php

namespace App\Http\Controllers\Admin\Role;

use App\DDD\Administration\Application\Query\GetAllRolesQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ListRolesController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): View
    {
        $roles = $this->queryBus->dispatch(GetAllRolesQuery::create());

        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }
}
