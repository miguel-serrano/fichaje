<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\UpdatePermissionCommand;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Administration\Domain\ValueObjects\PermissionId;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\Models\Permission as PermissionModel;
use Illuminate\Database\ConnectionInterface;

class UpdatePermissionCommandHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private AuthorizationServiceInterface $authorizationService,
        private ConnectionInterface $connection,
    ) {}

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(UpdatePermissionCommand $command): array
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManagePermissions->value, $command->authenticatedUserId);

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
