<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetApprovedHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

class GetApprovedHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetApprovedHolidaysQuery $query): GetApprovedHolidaysQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(HolidayPermission::ViewApproved->value, $query->authenticatedUserId->value());

        $holidays = $this->holidayRepository->findApproved();

        return new GetApprovedHolidaysQueryResponse($holidays);
    }
}
