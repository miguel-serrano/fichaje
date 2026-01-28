<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\UpdatePermissionCommand;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\Services\AuthorizationAuthorizationServiceInterface;
use App\DDD\Authorization\Domain\ValueObjects\PermissionId;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\Permission as PermissionModel;
use Illuminate\Database\ConnectionInterface;

class UpdatePermissionCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PermissionRepositoryInterface $permissionRepository,
        private AuthorizationAuthorizationServiceInterface $authorizationService,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(UpdatePermissionCommand $command): array
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->authorizationService->assertCanManagePermissions($authenticatedUser);

        $this->permissionRepository->findByIdOrFail(new PermissionId($command->permissionId));

        $this->connection->table(PermissionModel::tableName())
            ->where('id', $command->permissionId)
            ->update([
                'name' => $command->name,
                'description' => $command->description,
                'updated_at' => UnixTimestamp::now()->value(),
            ]);

        $updatedPermission = $this->permissionRepository->findByIdOrFail(new PermissionId($command->permissionId));

        return $updatedPermission->toArray();
    }
}
