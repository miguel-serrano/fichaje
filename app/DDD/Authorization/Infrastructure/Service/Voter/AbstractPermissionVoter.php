<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Infrastructure\Service\Voter;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;

abstract class AbstractPermissionVoter extends AbstractVoter
{
    public function __construct(
        protected readonly UserPermissionsCheckerInterface $permissionsChecker,
    ) {
    }

    protected function voteOnAttribute(int $userId, string $attribute, mixed $subject): bool
    {
        if ($this->permissionsChecker->isSuperAdmin($userId)) {
            return true;
        }

        return $this->permissionsChecker->hasPermission($userId, $attribute);
    }
}
