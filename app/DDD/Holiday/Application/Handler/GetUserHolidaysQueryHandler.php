<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetUserHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

class GetUserHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserHolidaysQuery $query): GetUserHolidaysQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(HolidayPermission::ViewOwn->value, $query->userId->value());

        $holidays = $this->holidayRepository->findByUserId($query->userId);

        return new GetUserHolidaysQueryResponse($holidays);
    }
}
