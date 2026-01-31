<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Command\ApproveHolidayRequestCommand;
use App\DDD\Holiday\Application\Service\HolidayNotifierService;
use App\DDD\Holiday\Application\Service\HolidayService;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

final class ApproveHolidayRequestCommandHandler
{
    public function __construct(
        private HolidayService $holidayService,
        private HolidayNotifierService $notifierService,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(ApproveHolidayRequestCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(HolidayPermission::Approve->value, $command->authenticatedUserId->value());

        $holidayRequest = $this->holidayService->approve($command->holidayRequestId);

        $this->notifierService->notifyHolidayApproved($holidayRequest);
    }
}
