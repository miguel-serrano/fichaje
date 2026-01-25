<?php

namespace App\Http\Controllers\Notification;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Notification\Application\Command\MarkNotificationAsReadCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkNotificationAsReadController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        $this->commandBus->dispatch(
            MarkNotificationAsReadCommand::create($id, $user->id()->value())
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
