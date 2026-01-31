<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Voter;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Infrastructure\Service\Voter\AbstractVoter;
use App\DDD\User\Domain\Permission\UserPermission;

/**
 * Voter para el bounded context User.
 *
 * Lógica especial:
 * - canView: superAdmin O el propio usuario (subject = targetUserId)
 * - canDelete: superAdmin Y target NO es superAdmin (subject = targetUserId)
 * - canList, canToggleActive: solo superAdmin
 * - Resto: por permisos estándar
 */
final class UserVoter extends AbstractVoter
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
            fn (UserPermission $case) => $case->value,
            UserPermission::cases()
        );
    }

    protected function voteOnAttribute(int $userId, string $attribute, mixed $subject): bool
    {
        if ($this->permissionsChecker->isSuperAdmin($userId)) {
            if ($attribute === UserPermission::Delete->value && null !== $subject) {
                return !$this->permissionsChecker->isSuperAdmin((int) $subject);
            }

            return true;
        }

        return match ($attribute) {
            UserPermission::View->value,
            UserPermission::ViewOwn->value => null !== $subject && $userId === (int) $subject,
            UserPermission::ToggleActive->value,
            UserPermission::Delete->value => false,
            default => $this->permissionsChecker->hasPermission($userId, $attribute),
        };
    }
}
