<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Domain\Exceptions\AccessDeniedException;
use App\DDD\Holiday\Application\Command\ApproveHolidayRequestCommand;
use App\DDD\Holiday\Domain\Exceptions\HolidayRequestNotFoundException;
use App\DDD\Holiday\Domain\Exceptions\HolidayRequestNotPendingException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ApproveHolidayController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id): RedirectResponse
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        try {
            $this->commandBus->dispatch(
                ApproveHolidayRequestCommand::create($authenticatedUser->id()->value(), $id)
            );
        } catch (HolidayRequestNotFoundException|HolidayRequestNotPendingException|AccessDeniedException $e) {
            return redirect()
                ->route('admin.holidays.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Solicitud de vacaciones aprobada.');
    }
}
