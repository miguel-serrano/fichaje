<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(GetUserByIdQuery $query): User
    {
        $targetUser = $this->userRepository->findByIdOrFail($query->targetUserId);

        $this->authorizationService->denyAccessUnlessGranted(UserPermission::View->value, $query->authenticatedUserId->value(), $query->targetUserId->value());

        return $targetUser;
    }
}
