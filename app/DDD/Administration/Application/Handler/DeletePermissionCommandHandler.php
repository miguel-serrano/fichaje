<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\DeletePermissionCommand;
use App\DDD\Administration\Domain\Exceptions\CannotDeleteSystemPermissionException;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class DeletePermissionCommandHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(DeletePermissionCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManagePermissions->value, $command->authenticatedUserId->value());

        $permission = $this->permissionRepository->findByIdOrFail($command->permissionId);

        if ($permission->isSystem()) {
            throw CannotDeleteSystemPermissionException::forPermission($permission->slug()->value());
        }

        $this->permissionRepository->delete($command->permissionId);
    }
}
