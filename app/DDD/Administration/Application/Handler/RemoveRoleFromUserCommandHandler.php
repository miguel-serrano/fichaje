<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\RemoveRoleFromUserCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\Models\UserRole;
use Illuminate\Database\ConnectionInterface;

class RemoveRoleFromUserCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
        private ConnectionInterface $connection,
    ) {
    }

    public function handle(RemoveRoleFromUserCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::AssignRoles->value, $command->authenticatedUserId);

        $role = $this->roleRepository->findBySlugOrFail(new RoleSlug($command->roleSlug));

        $this->connection->table(UserRole::tableName())
            ->where('user_id', $command->targetUserId)
            ->where('role_id', $role->id()->value())
            ->delete();
    }
}
