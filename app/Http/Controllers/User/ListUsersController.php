<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListUsersController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus
    ) {}

    public function __invoke(Request $request): View|JsonResponse
    {
        $users = $this->queryBus->dispatch(new GetAllUsersWithTimeQuery);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($users);
        }

        return view('users.index', [
            'users' => $users,
            'isAdmin' => true,
        ]);
    }
}
