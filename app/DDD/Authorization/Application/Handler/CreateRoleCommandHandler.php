<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\CreateRoleCommand;
use App\DDD\Authorization\Domain\Entity\Role;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleName;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class CreateRoleCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(CreateRoleCommand $command): array
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, AuthorizationPermission::ManageRoles->value);

        $role = Role::create(
            new RoleName($command->name),
            new RoleSlug($command->slug),
            $command->description,
            false,
            $command->hierarchy
        );

        $savedRole = $this->roleRepository->save($role);

        return $savedRole->toArray();
    }
}
