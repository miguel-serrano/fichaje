<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\UpdateRoleCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleId;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Role as RoleModel;
use Illuminate\Database\ConnectionInterface;

class UpdateRoleCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(UpdateRoleCommand $command): array
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->assertHasPermission($authenticatedUser, AuthorizationPermission::ManageRoles->value);

        $role = $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        $this->connection->table(RoleModel::tableName())
            ->where('id', $command->roleId)
            ->update([
                'name' => $command->name,
                'description' => $command->description,
                'hierarchy' => $command->hierarchy,
                'updated_at' => UnixTimestamp::now()->value(),
            ]);

        $updatedRole = $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        return $updatedRole->toArray();
    }
}
