<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\CreatePermissionCommand;
use App\DDD\Administration\Domain\Entity\Permission;
use App\DDD\Administration\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\PermissionName;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class CreatePermissionCommandHandler
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(CreatePermissionCommand $command): array
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManagePermissions->value, $command->authenticatedUserId);

        $permission = Permission::create(
            new PermissionName($command->name),
            new PermissionSlug($command->slug),
            BoundedContext::from($command->boundedContext),
            $command->description,
            false
        );

        $savedPermission = $this->permissionRepository->save($permission);

        return $savedPermission->toArray();
    }
}
