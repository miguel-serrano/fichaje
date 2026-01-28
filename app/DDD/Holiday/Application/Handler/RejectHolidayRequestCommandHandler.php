<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Holiday\Application\Service\HolidayNotifierService;
use App\DDD\Holiday\Application\Service\HolidayService;
use App\DDD\Holiday\Domain\Services\HolidayAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

final class RejectHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayService $holidayService,
        private HolidayNotifierService $notifierService,
        private UserRepositoryInterface $userRepository,
        private HolidayAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(RejectHolidayRequestCommand $command): void
    {
        $user = $this->userRepository->findByIdOrFail($command->authenticatedUserId);

        $this->authorizationService->assertCanRejectHoliday($user);

        $holidayRequest = $this->holidayService->reject($command->holidayRequestId);

        $this->notifierService->notifyHolidayRejected($holidayRequest);
    }
}
