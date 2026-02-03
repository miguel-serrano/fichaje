<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\UpdateRoleCommand;
use App\DDD\Administration\Domain\Event\RoleUpdatedEvent;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Shared\Domain\Event\EventBusInterface;

class UpdateRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
        private EventBusInterface $eventBus,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(UpdateRoleCommand $command): array
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId->value());

        $this->roleRepository->findByIdOrFail($command->roleId);

        $updatedRole = $this->roleRepository->update($command->roleId, $command->name, $command->description, $command->hierarchy);

        $this->eventBus->publish(new RoleUpdatedEvent(
            (string) $updatedRole->id()->value(),
            $updatedRole->name()->value(),
            $updatedRole->slug()->value(),
        ));

        return $updatedRole->toArray();
    }
}
