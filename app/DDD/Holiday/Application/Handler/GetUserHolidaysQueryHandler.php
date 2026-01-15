<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetUserHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetUserHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(GetUserHolidaysQuery $query): GetUserHolidaysQueryResponse
    {
        $user = $this->userRepository->findByIdOrFail($query->userId);

        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::ViewOwn->value);

        $holidays = $this->holidayRepository->findByUserId($query->userId);

        return new GetUserHolidaysQueryResponse($holidays);
    }
}
