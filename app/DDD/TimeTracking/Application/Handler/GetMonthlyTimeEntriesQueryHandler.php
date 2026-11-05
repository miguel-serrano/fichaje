<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\TimeTracking\Application\Query\GetMonthlyTimeEntriesQuery;
use App\DDD\TimeTracking\Application\Response\GetMonthlyTimeEntriesQueryResponse;
use App\DDD\TimeTracking\Domain\Interface\TimeEntryRepositoryInterface;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;

/**
 * Fichajes del usuario que empezaron dentro del mes indicado.
 */
final class GetMonthlyTimeEntriesQueryHandler
{
    public function __construct(
        private TimeEntryRepositoryInterface $timeEntryRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetMonthlyTimeEntriesQuery $query): GetMonthlyTimeEntriesQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ViewOwn->value, $query->userId->value());

        $monthStart = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $query->year, $query->month));
        $nextMonthStart = $monthStart->modify('first day of next month');

        return new GetMonthlyTimeEntriesQueryResponse(
            $this->timeEntryRepository->findByUserIdBetween(
                $query->userId,
                $monthStart->getTimestamp(),
                $nextMonthStart->getTimestamp(),
            )
        );
    }
}
