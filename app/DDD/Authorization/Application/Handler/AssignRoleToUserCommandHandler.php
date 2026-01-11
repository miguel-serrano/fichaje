<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\AssignRoleToUserCommand;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

class AssignRoleToUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    public function handle(AssignRoleToUserCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, AuthorizationPermission::AssignRoles->value);

        $role = $this->roleRepository->findBySlugOrFail(new RoleSlug($command->roleSlug));

        DB::table('user_role')->updateOrInsert(
            ['user_id' => $command->targetUserId, 'role_id' => $role->id()->value()],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }
}
