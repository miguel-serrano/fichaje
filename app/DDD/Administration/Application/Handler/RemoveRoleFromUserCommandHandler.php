<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\RemoveRoleFromUserCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class RemoveRoleFromUserCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(RemoveRoleFromUserCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::AssignRoles->value, $command->authenticatedUserId->value());

        $role = $this->roleRepository->findBySlugOrFail($command->roleSlug);

        $this->roleRepository->removeRole($command->targetUserId, $role->id());
    }
}
