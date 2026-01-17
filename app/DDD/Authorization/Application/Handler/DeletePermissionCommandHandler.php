<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\DeletePermissionCommand;
use App\DDD\Authorization\Domain\Exceptions\CannotDeleteSystemPermissionException;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\PermissionId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class DeletePermissionCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PermissionRepositoryInterface $permissionRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(DeletePermissionCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->assertHasPermission($authenticatedUser, AuthorizationPermission::ManagePermissions->value);

        $permission = $this->permissionRepository->findByIdOrFail(new PermissionId($command->permissionId));

        if ($permission->isSystem()) {
            throw CannotDeleteSystemPermissionException::forPermission($permission->slug()->value());
        }

        $this->permissionRepository->delete(new PermissionId($command->permissionId));
    }
}
