<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\Interface;

interface AuthorizableUserInterface
{
    public function id(): int;

    public function isSuperAdmin(): bool;

    public function hasPermission(string $permissionSlug): bool;

    /**
     * @return string[]
     */
    public function permissions(): array;
}
