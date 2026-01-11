<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\RemoveRoleFromUserCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class RemoveRoleFromUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
        private ConnectionInterface $connection,
    ) {
    }

    public function handle(RemoveRoleFromUserCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, AuthorizationPermission::AssignRoles->value);

        $role = $this->roleRepository->findBySlugOrFail(new RoleSlug($command->roleSlug));

        $this->connection->table(UserRole::tableName())
            ->where('user_id', $command->targetUserId)
            ->where('role_id', $role->id()->value())
            ->delete();
    }
}
