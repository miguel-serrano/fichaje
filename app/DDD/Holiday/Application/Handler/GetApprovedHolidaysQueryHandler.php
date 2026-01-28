<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetApprovedHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Services\HolidayAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetApprovedHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private HolidayAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetApprovedHolidaysQuery $query): GetApprovedHolidaysQueryResponse
    {
        $user = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->authorizationService->assertCanViewApprovedHolidays($user);

        $holidays = $this->holidayRepository->findApproved();

        return new GetApprovedHolidaysQueryResponse($holidays);
    }
}
