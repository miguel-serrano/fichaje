<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\Exceptions\OverlappingHolidayException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Http\Requests\StoreHolidayRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private PermissionCheckerInterface $permissionChecker,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function index(): View
    {
        $holidays = $this->queryBus->dispatch(
            new GetUserHolidaysQuery(Auth::id())
        );

        $user = $this->userRepository->findByIdOrFail(new UserId(Auth::id()));
        $canRequestHoliday = $this->permissionChecker->hasPermission($user, 'holiday.create');

        return view('holidays.index', [
            'holidays' => $holidays,
            'canRequestHoliday' => $canRequestHoliday,
        ]);
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        $user = $this->userRepository->findByIdOrFail(new UserId(Auth::id()));

        if (!$this->permissionChecker->hasPermission($user, 'holiday.create')) {
            return redirect()
                ->route('holidays.index')
                ->with('error', 'No tienes permisos para solicitar vacaciones.');
        }

        try {
            $this->commandBus->dispatch(
                new CreateHolidayRequestCommand(
                    Auth::id(),
                    $request->validated('start_date'),
                    $request->validated('end_date')
                )
            );

            return redirect()
                ->route('holidays.index')
                ->with('success', 'Solicitud de vacaciones enviada correctamente.');
        } catch (OverlappingHolidayException $e) {
            return redirect()
                ->route('holidays.index')
                ->with('error', $e->getMessage());
        } catch (InvalidHolidayDateRangeException $e) {
            return redirect()
                ->route('holidays.index')
                ->with('error', $e->getMessage());
        }
    }
}
