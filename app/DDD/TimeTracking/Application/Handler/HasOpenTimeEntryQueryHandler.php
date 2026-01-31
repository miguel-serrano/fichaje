<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\TimeTracking\Application\Query\HasOpenTimeEntryQuery;
use App\DDD\TimeTracking\Application\Response\HasOpenTimeEntryQueryResponse;
use App\DDD\TimeTracking\Application\Service\TimeTrackingService;
use App\DDD\TimeTracking\Domain\Permission\TimeTrackingPermission;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class HasOpenTimeEntryQueryHandler
{
    public function __construct(
        private TimeTrackingService $service,
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(HasOpenTimeEntryQuery $query): HasOpenTimeEntryQueryResponse
    {
        $user = $this->userRepository->findByUuidOrFail($query->userUuid);

        $this->authorizationService->denyAccessUnlessGranted(TimeTrackingPermission::ViewOwn->value, $user->id()->value());

        return new HasOpenTimeEntryQueryResponse(
            $this->service->hasOpenTimeEntry($query->userUuid->value())
        );
    }
}
