<?php

namespace App\Http\Controllers\Notification;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Notification\Application\Command\MarkAllNotificationsAsReadCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkAllNotificationsAsReadController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        $this->commandBus->dispatch(
            MarkAllNotificationsAsReadCommand::create($user->id()->value())
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }
}
