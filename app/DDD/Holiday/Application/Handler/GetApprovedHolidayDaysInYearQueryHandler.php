<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Query\GetApprovedHolidayDaysInYearQuery;
use App\DDD\Holiday\Application\Response\GetApprovedHolidayDaysInYearQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

/**
 * Días de vacaciones aprobadas que caen dentro del año: una solicitud que cruza
 * el cambio de año reparte sus días entre ambos años.
 */
final class GetApprovedHolidayDaysInYearQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetApprovedHolidayDaysInYearQuery $query): GetApprovedHolidayDaysInYearQueryResponse
    {
        $this->authorizationService->denyAccessUnlessGranted(HolidayPermission::ViewOwn->value, $query->userId->value());

        $days = 0;

        foreach ($this->holidayRepository->findByUserId($query->userId) as $holiday) {
            if ($holiday->isApproved()) {
                $days += $holiday->dateRange()->daysInYear($query->year);
            }
        }

        return new GetApprovedHolidayDaysInYearQueryResponse($days);
    }
}
