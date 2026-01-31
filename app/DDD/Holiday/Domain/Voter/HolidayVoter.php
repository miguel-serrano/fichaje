<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Voter;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\Service\Voter\AbstractVoter;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

final class HolidayVoter extends AbstractVoter
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
            fn (HolidayPermission $case) => $case->value,
            HolidayPermission::cases()
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
