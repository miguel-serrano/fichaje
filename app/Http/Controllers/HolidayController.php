<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\Exceptions\OverlappingHolidayException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Requests\StoreHolidayRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function index(): View
    {
        $holidays = $this->queryBus->dispatch(
            new GetUserHolidaysQuery(Auth::id())
        );

        return view('holidays.index', [
            'holidays' => $holidays,
        ]);
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
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
