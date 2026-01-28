<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Command\CreateHolidayRequestCommand;
use App\DDD\Holiday\Application\Service\HolidayNotifierService;
use App\DDD\Holiday\Application\Service\HolidayService;
use App\DDD\Holiday\Domain\Services\HolidayAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

final class CreateHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayService $holidayService,
        private HolidayNotifierService $notifierService,
        private UserRepositoryInterface $userRepository,
        private HolidayAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(CreateHolidayRequestCommand $command): void
    {
        $user = $this->userRepository->findByIdOrFail($command->userId);

        $this->authorizationService->assertCanRequestHoliday($user);

        $dateRange = $this->holidayService->createRequest($user, $command->startDate, $command->endDate);

        $this->notifierService->notifyHolidayRequested($user, $dateRange);
    }
}
