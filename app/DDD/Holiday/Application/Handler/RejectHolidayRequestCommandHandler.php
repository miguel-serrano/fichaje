<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Holiday\Application\Service\HolidayNotifierService;
use App\DDD\Holiday\Application\Service\HolidayService;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

final class RejectHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayService $holidayService,
        private HolidayNotifierService $notifierService,
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(RejectHolidayRequestCommand $command): void
    {
        $user = $this->userRepository->findByIdOrFail($command->authenticatedUserId);

        $this->permissionChecker->ensureHasPermission($user, HolidayPermission::Reject->value);

        $holidayRequest = $this->holidayService->reject($command->holidayRequestId);

        $this->notifierService->notifyHolidayRejected($holidayRequest);
    }
}
