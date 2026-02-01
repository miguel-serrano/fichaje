<?php

declare(strict_types=1);

namespace App\DDD\Administration\Application\Command;

use App\DDD\Administration\Domain\ValueObjects\BoundedContext;
use App\DDD\Administration\Domain\ValueObjects\Description;
use App\DDD\Administration\Domain\ValueObjects\PermissionName;
use App\DDD\Administration\Domain\ValueObjects\PermissionSlug;
use App\DDD\User\Domain\ValueObjects\UserId;

final class CreatePermissionCommand
{
    private function __construct(
        public readonly UserId $authenticatedUserId,
        public readonly PermissionName $name,
        public readonly PermissionSlug $slug,
        public readonly BoundedContext $boundedContext,
        public readonly ?Description $description,
    ) {
    }

    public static function create(
        int $authenticatedUserId,
        string $name,
        string $slug,
        string $boundedContext,
        ?string $description = null,
    ): self {
        return new self(
            authenticatedUserId: UserId::make($authenticatedUserId),
            name: PermissionName::make($name),
            slug: PermissionSlug::make($slug),
            boundedContext: BoundedContext::from($boundedContext),
            description: null !== $description ? Description::make($description) : null,
        );
    }
}
