<?php

namespace App\Http\Controllers\Bienvenido;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BienvenidoController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function index(): View
    {
        return view('bienvenida');
    }

    public function acceptTerms(): JsonResponse
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        User::query()
            ->where('id', $authenticatedUser->id()->value())
            ->update(['accepted_terms' => true]);

        return response()->json(['success' => true]);
    }
}
