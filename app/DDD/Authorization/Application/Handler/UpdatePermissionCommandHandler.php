<?php

namespace App\DDD\Authorization\Application\Handler;

use App\DDD\Authorization\Application\Command\UpdatePermissionCommand;
use App\DDD\Authorization\Domain\Interface\PermissionRepositoryInterface;
use App\DDD\Authorization\Domain\Permission\AuthorizationPermission;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Authorization\Domain\ValueObjects\PermissionId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use Illuminate\Support\Facades\DB;

class UpdatePermissionCommandHandler
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
    public function handle(UpdatePermissionCommand $command): array
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail(
            new UserId($command->authenticatedUserId)
        );

        $this->permissionChecker->ensureHasPermission($authenticatedUser, AuthorizationPermission::ManagePermissions->value);

        $this->permissionRepository->findByIdOrFail(new PermissionId($command->permissionId));

        DB::table('permissions')
            ->where('id', $command->permissionId)
            ->update([
                'name' => $command->name,
                'description' => $command->description,
                'updated_at' => now(),
            ]);

        $updatedPermission = $this->permissionRepository->findByIdOrFail(new PermissionId($command->permissionId));

        return $updatedPermission->toArray();
    }
}
