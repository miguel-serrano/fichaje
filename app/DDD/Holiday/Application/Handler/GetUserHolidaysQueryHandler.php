<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetUserHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    /**
     * @return HolidayRequest[]
     */
    public function handle(GetUserHolidaysQuery $query): array
    {
        $userId = new UserId($query->userId);
        $user = $this->userRepository->findByIdOrFail($userId);
        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::ViewOwn->value);

        return $this->holidayRepository->findByUserId($userId);
    }
}
