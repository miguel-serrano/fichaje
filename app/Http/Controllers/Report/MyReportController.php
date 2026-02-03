<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
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

        $hoursData = $this->getMonthlyHours($authenticatedUser);
        $holidaysData = $this->getYearlyHolidays($userId);

        return view('report.me', [
            'user' => $authenticatedUser,
            'hoursWorked' => $hoursData['worked'],
            'hoursTarget' => 160,
            'approvedDays' => $holidaysData['approved'],
            'holidaysTarget' => 22,
        ]);
    }

    /**
     * @return array{worked: float}
     */
    private function getMonthlyHours(mixed $user): array
    {
        $monthlyEntries = array_filter(
            $user->timeEntries(),
            fn (TimeEntry $r) => date('Y-m', $r->startTime()) === date('Y-m')
        );

        $totalSeconds = 0;
        foreach ($monthlyEntries as $entry) {
            if (!$entry->isOpen()) {
                $totalSeconds += $entry->endTime() - $entry->startTime();
            }
        }

        return [
            'worked' => round($totalSeconds / 3600, 1),
        ];
    }

    /**
     * @return array{approved: int}
     */
    private function getYearlyHolidays(int $userId): array
    {
        $holidaysResponse = $this->queryBus->dispatch(
            GetUserHolidaysQuery::create($userId)
        );

        $currentYear = (int) date('Y');
        $approvedDays = 0;

        foreach ($holidaysResponse->holidays() as $holiday) {
            if ($holiday->isApproved()) {
                $startYear = (int) date('Y', $holiday->dateRange()->startDate());
                if ($startYear === $currentYear) {
                    $approvedDays += $holiday->dateRange()->totalDays();
                }
            }
        }

        return [
            'approved' => $approvedDays,
        ];
    }
}
