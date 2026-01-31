<?php

declare(strict_types=1);

namespace App\Http\Controllers\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\Exceptions\OverlappingHolidayException;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHolidayRequest;
use Illuminate\Http\RedirectResponse;

class StoreHolidayController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function __invoke(StoreHolidayRequest $request): RedirectResponse
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());
        $userId = $authenticatedUser->id()->value();

        if (!$this->authorizationService->isGranted(HolidayPermission::Request->value, $userId)) {
            return redirect()
                ->route('holidays.index')
                ->with('error', 'No tienes permisos para solicitar vacaciones.');
        }

        try {
            $this->commandBus->dispatch(
                CreateHolidayRequestCommand::create(
                    $userId,
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
