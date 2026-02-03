<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\SyncPermissionsToRoleCommand;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;

class SyncPermissionsToRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
        private EventBusInterface $eventBus,
    ) {
    }

    public function handle(SyncPermissionsToRoleCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId->value());

        $role = $this->roleRepository->findByIdOrFail($command->roleId);

        $previousSlugs = array_map(
            fn ($p) => $p->slug()->value(),
            $role->permissions()
        );

        $this->roleRepository->syncPermissions(
            $command->roleId,
            $command->permissionIds->toPrimitives()
        );

        $updatedRole = $this->roleRepository->findByIdOrFail($command->roleId);
        $currentSlugs = array_map(
            fn ($p) => $p->slug()->value(),
            $updatedRole->permissions()
        );

        $added = array_values(array_diff($currentSlugs, $previousSlugs));
        $removed = array_values(array_diff($previousSlugs, $currentSlugs));
        $permissionsSynced = [];

        if ($added) {
            $permissionsSynced['added'] = $added;
        }
        if ($removed) {
            $permissionsSynced['removed'] = $removed;
        }

        $updatedRole->recordPermissionsSynced($permissionsSynced ?: null);
        $this->eventBus->publish(...$updatedRole->pullDomainEvents());
    }
}
