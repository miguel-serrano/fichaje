<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\DDD\Holiday\Application\Command\ApproveHolidayRequestCommand;
use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HolidayAdminController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function index(): View
    {
        $pendingResponse = $this->queryBus->dispatch(
            GetPendingHolidaysQuery::create(Auth::id())
        );

        $approvedResponse = $this->queryBus->dispatch(
            GetApprovedHolidaysQuery::create(Auth::id())
        );

        $pendingWithUsers = [];
        foreach ($pendingResponse->holidays() as $holiday) {
            $user = $this->userRepository->findById($holiday->userId());
            $pendingWithUsers[] = [
                'holiday' => $holiday,
                'user' => $user,
            ];
        }

        $approvedWithUsers = [];
        foreach ($approvedResponse->holidays() as $holiday) {
            $user = $this->userRepository->findById($holiday->userId());
            $approvedWithUsers[] = [
                'holiday' => $holiday,
                'user' => $user,
            ];
        }

        return view('admin.holidays.index', [
            'pendingWithUsers' => $pendingWithUsers,
            'approvedWithUsers' => $approvedWithUsers,
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $this->commandBus->dispatch(
            ApproveHolidayRequestCommand::create(Auth::id(), $id)
        );

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Solicitud de vacaciones aprobada.');
    }

    public function reject(int $id): RedirectResponse
    {
        $this->commandBus->dispatch(
            RejectHolidayRequestCommand::create(Auth::id(), $id)
        );

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Solicitud de vacaciones rechazada.');
    }
}
