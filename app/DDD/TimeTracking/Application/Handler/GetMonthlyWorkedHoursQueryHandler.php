<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\TimeTracking\Application\Query\GetMonthlyWorkedHoursQuery;
use App\DDD\TimeTracking\Application\Response\GetMonthlyWorkedHoursQueryResponse;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;

/**
 * Horas trabajadas en fichajes cerrados que empezaron dentro del mes indicado.
 */
final class GetMonthlyWorkedHoursQueryHandler
{
    public function __construct(
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetMonthlyWorkedHoursQuery $query): GetMonthlyWorkedHoursQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ViewOwn->value, $query->userId->value());

        $monthStart = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $query->year, $query->month));
        $nextMonthStart = $monthStart->modify('first day of next month');

        $seconds = $this->timeEntryRepository->getClosedWorkedSecondsBetween(
            $query->userId,
            $monthStart->getTimestamp(),
            $nextMonthStart->getTimestamp(),
        );

        return new GetMonthlyWorkedHoursQueryResponse($seconds);
    }
}
