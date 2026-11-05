<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\Voter;

use App\DDD\Authorization\Domain\Voter\AbstractPermissionVoter;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;

/**
 * Voter para el bounded context Holiday.
 *
 * - Approve, Reject: nadie puede revisar su propia solicitud salvo el superAdmin (subject = userId del solicitante).
 * - Resto: lógica estándar (superAdmin o permiso por rol).
 */
final class HolidayVoter extends AbstractPermissionVoter
{
    private const REVIEW_ATTRIBUTES = [
        HolidayPermission::Approve->value,
        HolidayPermission::Reject->value,
    ];

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
        if ($this->isReviewingOwnRequest($userId, $attribute, $subject)) {
            return false;
        }

        return parent::voteOnAttribute($userId, $attribute, $subject);
    }

    private function isReviewingOwnRequest(int $userId, string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::REVIEW_ATTRIBUTES, true)
            && null !== $subject
            && $userId === (int) $subject
            && !$this->permissionsChecker->isSuperAdmin($userId);
    }
}
