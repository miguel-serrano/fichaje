<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\SyncPermissionsToRoleCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class SyncPermissionsToRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(SyncPermissionsToRoleCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId);

        $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        $this->roleRepository->syncPermissions(
            new RoleId($command->roleId),
            $command->permissionIds
        );
    }
}
