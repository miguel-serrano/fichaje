<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\UpdatePermissionCommand;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class UpdatePermissionCommandHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(UpdatePermissionCommand $command): array
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManagePermissions->value, $command->authenticatedUserId->value());

        $this->permissionRepository->findByIdOrFail($command->permissionId);

        $updatedPermission = $this->permissionRepository->update($command->permissionId, $command->name, $command->description);

        return $updatedPermission->toArray();
    }
}
