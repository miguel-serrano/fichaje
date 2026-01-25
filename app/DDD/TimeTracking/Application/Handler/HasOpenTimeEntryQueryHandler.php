<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\TimeTracking\Application\Response\HasOpenTimeEntryQueryResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Services\TimeTrackingAuthorizationServiceInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class HasOpenTimeEntryQueryHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private TimeTrackingAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(HasOpenTimeEntryQuery $query): HasOpenTimeEntryQueryResponse
    {
        $user = $this->userRepository->findByUuidOrFail($query->userUuid);

        $this->authorizationService->assertCanViewTimeEntry($user);

        return new HasOpenTimeEntryQueryResponse(
            $this->service->hasOpenTimeEntry($query->userUuid->value())
        );
    }
}
