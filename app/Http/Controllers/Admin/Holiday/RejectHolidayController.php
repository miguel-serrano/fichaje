<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class RejectHolidayController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id): RedirectResponse
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        $this->commandBus->dispatch(
            RejectHolidayRequestCommand::create($authenticatedUser->id()->value(), $id)
        );

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Solicitud de vacaciones rechazada.');
    }
}
