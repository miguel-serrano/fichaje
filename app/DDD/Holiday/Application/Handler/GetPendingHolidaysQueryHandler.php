<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetPendingHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetPendingHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(GetPendingHolidaysQuery $query): GetPendingHolidaysQueryResponse
    {
        $user = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::ViewPending->value);

        $holidays = $this->holidayRepository->findPending();

        return new GetPendingHolidaysQueryResponse($holidays);
    }
}
