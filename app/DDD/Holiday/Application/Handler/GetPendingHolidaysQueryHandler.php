<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetPendingHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

class GetPendingHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetPendingHolidaysQuery $query): GetPendingHolidaysQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(HolidayPermission::ViewPending->value, $query->authenticatedUserId->value());

        $holidays = $this->holidayRepository->findPending();

        return new GetPendingHolidaysQueryResponse($holidays);
    }
}
