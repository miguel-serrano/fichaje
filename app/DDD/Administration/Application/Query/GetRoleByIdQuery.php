<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Query;

use App\DDD\Administration\Domain\ValueObjects\RoleId;

final class GetRoleByIdQuery
{
    private function __construct(
        public readonly RoleId $roleId,
    ) {
    }

    public static function create(int $roleId): self
    {
        return new self(
            roleId: RoleId::make($roleId),
        );
    }
}
