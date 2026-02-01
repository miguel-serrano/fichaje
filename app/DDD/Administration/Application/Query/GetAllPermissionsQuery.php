<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Query;

use App\DDD\Administration\Domain\ValueObjects\BoundedContext;

final class GetAllPermissionsQuery
{
    private function __construct(
        public readonly ?BoundedContext $boundedContext = null,
    ) {
    }

    public static function create(?string $boundedContext = null): self
    {
        return new self(
            boundedContext: null !== $boundedContext ? BoundedContext::from($boundedContext) : null,
        );
    }
}
