<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Command\RejectHolidayRequestCommand;
use App\DDD\Holiday\Application\Service\HolidayNotifierService;
use App\DDD\Holiday\Application\Service\HolidayService;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

final class RejectHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayService $holidayService,
        private HolidayNotifierService $notifierService,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(RejectHolidayRequestCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(HolidayPermission::Reject->value, $command->authenticatedUserId->value());

        $holidayRequest = $this->holidayService->reject($command->holidayRequestId);

        $this->notifierService->notifyHolidayRejected($holidayRequest);
    }
}
