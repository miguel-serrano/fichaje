<?php

namespace App\DDD\Administration\Application\Handler;

use App\DDD\Administration\Application\Command\DeleteRoleCommand;
use App\DDD\Administration\Domain\Exceptions\CannotDeleteSystemRoleException;
use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Administration\Domain\ValueObjects\RoleId;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;

class DeleteRoleCommandHandler
{
    public function __construct(
        private RoleRepositoryInterface $roleRepository,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(DeleteRoleCommand $command): void
    {
        $this->authorizationService->denyAccessUnlessGranted(AdministrationPermission::ManageRoles->value, $command->authenticatedUserId);

        $role = $this->roleRepository->findByIdOrFail(new RoleId($command->roleId));

        if ($role->isSystem()) {
            throw CannotDeleteSystemRoleException::forRole($role->slug()->value());
        }

        $this->roleRepository->delete(new RoleId($command->roleId));
    }
}
