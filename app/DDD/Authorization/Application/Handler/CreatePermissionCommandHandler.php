<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\CreatePermissionCommand;
use App\DDD\Authorization\Domain\Entity\Permission;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\BoundedContext;
use App\DDD\Authorization\Domain\ValueObjects\PermissionName;
use App\DDD\Authorization\Domain\ValueObjects\PermissionSlug;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class CreatePermissionCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PermissionRepositoryInterface $permissionRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, slug: string, bounded_context: string, description: string|null, is_system: bool}
     */
    public function handle(CreatePermissionCommand $command): array
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, AuthorizationPermission::ManagePermissions->value);

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
