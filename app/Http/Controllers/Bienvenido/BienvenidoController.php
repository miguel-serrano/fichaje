<?php

namespace App\Http\Controllers\Bienvenido;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\AcceptTermsCommand;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BienvenidoController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function index(): View
    {
        return view('bienvenida');
    }

    public function acceptTerms(): JsonResponse
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        $this->commandBus->dispatch(AcceptTermsCommand::create($authenticatedUser->id()->value()));

        return response()->json(['success' => true]);
    }
}
