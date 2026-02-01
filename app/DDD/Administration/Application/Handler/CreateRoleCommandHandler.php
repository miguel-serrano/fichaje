<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\CreateRoleCommand;
use App\DDD\Administration\Domain\Entity\Role;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class CreateRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, description: string|null, is_system: bool, hierarchy: int, permissions: array}
     */
    public function handle(CreateRoleCommand $command): array
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId->value());

        $role = Role::create(
            $command->name,
            $command->slug,
            $command->description?->value(),
            false,
            $command->hierarchy->value()
        );

        $savedRole = $this->roleRepository->save($role);

        return $savedRole->toArray();
    }
}
