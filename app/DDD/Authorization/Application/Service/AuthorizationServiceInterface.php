<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Application\Service;

use App\DDD\Authorization\Domain\Exception\AccessDeniedException;

interface AuthorizationServiceInterface
{
    public function isGranted(string $attribute, int $userId, mixed $subject = null): bool;

    /**
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(string $attribute, int $userId, mixed $subject = null): void;
}
