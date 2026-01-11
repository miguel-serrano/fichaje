<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetApprovedHolidaysQueryHandler
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
    public function handle(GetApprovedHolidaysQuery $query): array
    {
        $user = $this->userRepository->findByIdOrFail(new UserId($query->authenticatedUserId));
        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::ViewApproved->value);

        return $this->holidayRepository->findApproved();
    }
}
