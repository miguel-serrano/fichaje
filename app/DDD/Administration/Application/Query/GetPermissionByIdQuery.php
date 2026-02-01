<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Query;

use App\DDD\Administration\Domain\ValueObjects\PermissionId;

final class GetPermissionByIdQuery
{
    private function __construct(
        public readonly PermissionId $permissionId,
    ) {
    }

    public static function create(int $permissionId): self
    {
        return new self(
            permissionId: PermissionId::make($permissionId),
        );
    }
}
