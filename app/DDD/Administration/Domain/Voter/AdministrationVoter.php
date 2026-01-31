<?php

declare(strict_types=1);

namespace App\DDD\Administration\Domain\Voter;

use App\DDD\Administration\Domain\Permission\AdministrationPermission;
use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\Service\Voter\AbstractVoter;

final class AdministrationVoter extends AbstractVoter
{
    public function __construct(
        private readonly UserPermissionsCheckerInterface $permissionsChecker,
    ) {
    }

    /**
     * @return string[]
     */
    public function supportedAttributes(): array
    {
        return array_map(
            fn (AdministrationPermission $case) => $case->value,
            AdministrationPermission::cases()
        );
    }

    protected function voteOnAttribute(int $userId, string $attribute, mixed $subject): bool
    {
        if ($this->permissionsChecker->isSuperAdmin($userId)) {
            return true;
        }

        return $this->permissionsChecker->hasPermission($userId, $attribute);
    }
}
