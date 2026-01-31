<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Administration\Application\Query\GetAllPermissionsQuery;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ListPermissionsController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): View
    {
        $permissions = $this->queryBus->dispatch(new GetAllPermissionsQuery());

        $permissionsByContext = collect($permissions)->groupBy('bounded_context')->toArray();

        $contexts = array_column(BoundedContext::cases(), 'value');

        return view('admin.permissions.index', [
            'permissionsByContext' => $permissionsByContext,
            'contexts' => $contexts,
        ]);
    }
}
