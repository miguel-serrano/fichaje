<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ListUsersController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(): View
    {
        $users = $this->queryBus->dispatch(new GetAllUsersWithTimeQuery);

        return view('users.index', [
            'users' => $users,
            'isAdmin' => true,
        ]);
    }
}
