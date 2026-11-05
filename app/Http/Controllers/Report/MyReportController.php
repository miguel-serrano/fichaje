<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Holiday\Application\Query\GetApprovedHolidayDaysInYearQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Application\Query\GetDailyHoursHistoryQuery;
use App\DDD\TimeTracking\Application\Query\GetMonthlyWorkedHoursQuery;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MyReportController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function __invoke(): View
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());
        $userId = $authenticatedUser->id()->value();
        $now = new \DateTimeImmutable();

        $monthlyHours = $this->queryBus->dispatch(
            GetMonthlyWorkedHoursQuery::create($userId, (int) $now->format('Y'), (int) $now->format('n'))
        );

        $approvedHolidayDays = $this->queryBus->dispatch(
            GetApprovedHolidayDaysInYearQuery::create($userId, (int) $now->format('Y'))
        );

        $chartData = $this->queryBus->dispatch(
            GetDailyHoursHistoryQuery::create(userId: $userId, days: 0)
        )->response();

        return view('report.me', [
            'user' => $authenticatedUser,
            'hoursWorked' => $monthlyHours->hours(),
            'hoursTarget' => config('users.report.monthly_hours_target'),
            'approvedDays' => $approvedHolidayDays->days(),
            'holidaysTarget' => config('users.report.yearly_holidays_target'),
            'chartData' => $chartData,
        ]);
    }
}
