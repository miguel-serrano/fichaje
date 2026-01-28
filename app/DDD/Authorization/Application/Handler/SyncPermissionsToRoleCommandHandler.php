<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\SyncPermissionsToRoleCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Services\AuthorizationAuthorizationServiceInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class SyncPermissionsToRoleCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(SyncPermissionsToRoleCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->authorizationService->assertCanManageRoles($authenticatedUser);

        $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        $this->roleRepository->syncPermissions(
            new RoleId($command->roleId),
            $command->permissionIds
        );
    }
}
