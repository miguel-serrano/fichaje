<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\AssignRoleToUserCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class AssignRoleToUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
        private ConnectionInterface $connection,
    ) {
    }

    public function handle(AssignRoleToUserCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->assertHasPermission($authenticatedUser, AuthorizationPermission::AssignRoles->value);

        $role = $this->roleRepository->findBySlugOrFail(new RoleSlug($command->roleSlug));

        $this->connection->table(UserRole::tableName())->updateOrInsert(
            ['user_id' => $command->targetUserId, 'role_id' => $role->id()->value()],
            ['created_at' => UnixTimestamp::now()->value(), 'updated_at' => UnixTimestamp::now()->value()]
        );
    }
}
