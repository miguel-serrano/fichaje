<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\SyncPermissionsToRoleCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class SyncPermissionsToRoleCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(SyncPermissionsToRoleCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->assertHasPermission($authenticatedUser, AuthorizationPermission::ManageRoles->value);

        $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        $this->roleRepository->syncPermissions(
            new RoleId($command->roleId),
            $command->permissionIds
        );
    }
}
