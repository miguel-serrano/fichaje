<?php

declare(strict_types=1);

namespace App\Http\Controllers\Holiday;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\Exceptions\OverlappingHolidayException;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StoreHolidayController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private PermissionCheckerInterface $permissionChecker,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(StoreHolidayRequest $request): RedirectResponse
    {
        $user = $this->userRepository->findByIdOrFail(UserId::make(Auth::id()));

        if (! $this->permissionChecker->hasPermission($user, HolidayPermission::Request->value)) {
            return redirect()
                ->route('holidays.index')
                ->with('error', 'No tienes permisos para solicitar vacaciones.');
        }

        try {
            $this->commandBus->dispatch(
                CreateHolidayRequestCommand::create(
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
