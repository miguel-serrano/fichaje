<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Command\ApproveHolidayRequestCommand;
use App\DDD\Holiday\Application\Service\HolidayNotifierService;
use App\DDD\Holiday\Application\Service\HolidayService;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

final class ApproveHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayService $holidayService,
        private HolidayNotifierService $notifierService,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(ApproveHolidayRequestCommand $command): void
    {
        $user = $this->userRepository->findByIdOrFail($command->authenticatedUserId);

        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::Approve->value);

        $holidayRequest = $this->holidayService->approve($command->holidayRequestId);

        $this->notifierService->notifyHolidayApproved($holidayRequest);
    }
}
