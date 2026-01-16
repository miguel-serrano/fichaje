<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Holiday;

use App\DDD\Holiday\Application\Command\ApproveHolidayRequestCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ApproveHolidayController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(int $id): RedirectResponse
    {
        $this->commandBus->dispatch(
            ApproveHolidayRequestCommand::create(Auth::id(), $id)
        );

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Solicitud de vacaciones aprobada.');
    }
}
