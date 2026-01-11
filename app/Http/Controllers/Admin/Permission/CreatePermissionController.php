<?php

namespace App\Http\Controllers\Admin\Permission;

use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CreatePermissionController extends Controller
{
    public function __invoke(): View
    {
        $contexts = array_column(BoundedContext::cases(), 'value');

        return view('admin.permissions.create', [
            'contexts' => $contexts,
        ]);
    }
}
