<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\UpdateRoleCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\Models\Role as RoleModel;
use Illuminate\Database\ConnectionInterface;

class UpdateRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(UpdateRoleCommand $command): array
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId->value());

        $this->roleRepository->findByIdOrFail($command->roleId);

        $this->connection->table(RoleModel::tableName())
            ->where('id', $command->roleId->value())
            ->update([
                'name' => $command->name->value(),
                'description' => $command->description?->value(),
                'hierarchy' => $command->hierarchy->value(),
                'updated_at' => UnixTimestamp::now()->value(),
            ]);

        $updatedRole = $this->roleRepository->findByIdOrFail($command->roleId);

        return $updatedRole->toArray();
    }
}
