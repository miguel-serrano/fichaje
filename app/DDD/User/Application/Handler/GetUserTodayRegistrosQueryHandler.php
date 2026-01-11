<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Permission\UserPermission;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetUserTodayRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PermissionCheckerInterface $permissionChecker,
    ) {
    }

    /** @return array<array-key, mixed> */
    public function handle(GetUserTodayRegistrosQuery $query): array
    {
        $userId = new UserId($query->getUserId());
        $user = $this->userRepository->findByIdOrFail($userId);
        $this->permissionChecker->ensureHasPermission($user, UserPermission::ViewOwn->value);

        return $this->userRepository->findTodayTimeEntriesByUserId($userId);
    }
}
