<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetPendingHolidaysQueryHandler
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
    public function handle(GetPendingHolidaysQuery $query): array
    {
        $user = $this->userRepository->findByIdOrFail(new UserId($query->authenticatedUserId));

        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::ViewPending->value);

        return $this->holidayRepository->findPending();
    }
}
