<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CreateRoleController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.roles.create');
    }
}
