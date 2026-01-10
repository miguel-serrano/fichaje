<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\RemoveRoleFromUserCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

class RemoveRoleFromUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(RemoveRoleFromUserCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, 'authorization.assign_roles');

        $role = $this->roleRepository->findBySlugOrFail(new RoleSlug($command->roleSlug));

        DB::table('user_role')
            ->where('user_id', $command->targetUserId)
            ->where('role_id', $role->id()->value())
            ->delete();
    }
}
