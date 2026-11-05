<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Voter;

use App\DDD\Authorization\Domain\Interface\UserPermissionsCheckerInterface;
use App\DDD\Authorization\Domain\Voter\AbstractVoter;
use App\DDD\User\Domain\Permission\UserPermission;

/**
 * Voter para el bounded context User.
 *
 * Lógica especial:
 * - canView: superAdmin O el propio usuario (subject = targetUserId) O permiso user.view por rol
 * - canViewOwn: superAdmin O el propio usuario
 * - canDelete, canToggleActive: (superAdmin O permiso por rol)
 *   Y target NO es el propio usuario Y target NO es superAdmin (subject = targetUserId)
 * - Resto: por permisos estándar
 */
final class UserVoter extends AbstractVoter
{
    private const TARGET_MANAGEMENT_ATTRIBUTES = [
        UserPermission::Delete->value,
        UserPermission::ToggleActive->value,
    ];

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
        if (in_array($attribute, self::TARGET_MANAGEMENT_ATTRIBUTES, true)) {
            return $this->canManageTarget($userId, $attribute, $subject);
        }

        if ($this->permissionsChecker->isSuperAdmin($userId)) {
            return true;
        }

        return match ($attribute) {
            UserPermission::View->value => $this->isOwnUser($userId, $subject)
                || $this->permissionsChecker->hasRolePermission($userId, $attribute),
            UserPermission::ViewOwn->value => $this->isOwnUser($userId, $subject),
            default => $this->permissionsChecker->hasRolePermission($userId, $attribute),
        };
    }

    private function isOwnUser(int $userId, mixed $subject): bool
    {
        return null !== $subject && $userId === (int) $subject;
    }

    private function canManageTarget(int $userId, string $attribute, mixed $subject): bool
    {
        if (null !== $subject) {
            $targetUserId = (int) $subject;

            if ($userId === $targetUserId || $this->permissionsChecker->isSuperAdmin($targetUserId)) {
                return false;
            }
        }

        return $this->permissionsChecker->isSuperAdmin($userId)
            || $this->permissionsChecker->hasRolePermission($userId, $attribute);
    }
}
