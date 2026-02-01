<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\AssignRoleToUserCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\ValueObject\UnixTimestamp;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class AssignRoleToUserCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
        private ConnectionInterface $connection,
    ) {
    }

    public function handle(AssignRoleToUserCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::AssignRoles->value, $command->authenticatedUserId->value());

        $role = $this->roleRepository->findBySlugOrFail($command->roleSlug);

        $this->connection->table(UserRole::tableName())->updateOrInsert(
            ['user_id' => $command->targetUserId->value(), 'role_id' => $role->id()->value()],
            ['created_at' => UnixTimestamp::now()->value(), 'updated_at' => UnixTimestamp::now()->value()]
        );
    }
}
