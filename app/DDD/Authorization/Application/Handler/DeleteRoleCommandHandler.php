<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\DeleteRoleCommand;
use App\DDD\Authorization\Domain\Exceptions\CannotDeleteSystemRoleException;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class DeleteRoleCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(DeleteRoleCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, AuthorizationPermission::ManageRoles->value);

        $role = $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        if ($role->isSystem()) {
            throw CannotDeleteSystemRoleException::forRole($role->slug()->value());
        }

        $this->roleRepository->delete(new RoleId($command->roleId));
    }
}
