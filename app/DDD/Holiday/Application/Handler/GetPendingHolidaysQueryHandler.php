<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Handler;

use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Holiday\Application\Response\GetPendingHolidaysQueryResponse;
use App\DDD\Holiday\Domain\Interface\HolidayRepositoryInterface;
use App\DDD\Holiday\Domain\Services\HolidayAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetPendingHolidaysQueryHandler
{
    public function __construct(
        private HolidayRepositoryInterface $holidayRepository,
        private UserRepositoryInterface $userRepository,
        private HolidayAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetPendingHolidaysQuery $query): GetPendingHolidaysQueryResponse
    {
        $user = $this->userRepository->findByIdOrFail($query->authenticatedUserId);

        $this->authorizationService->assertCanViewPendingHolidays($user);

        $holidays = $this->holidayRepository->findPending();

        return new GetPendingHolidaysQueryResponse($holidays);
    }
}
