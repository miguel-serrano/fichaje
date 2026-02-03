<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\DeleteRoleCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class DeleteRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(DeleteRoleCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId->value());

        $role = $this->roleRepository->findByIdOrFail($command->roleId);

        $role->assertCanDelete();

        $this->roleRepository->delete($command->roleId);
    }
}
