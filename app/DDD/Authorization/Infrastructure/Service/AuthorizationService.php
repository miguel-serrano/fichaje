<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Infrastructure\Service;

use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Authorization\Domain\Exception\AccessDeniedException;
use App\DDD\Authorization\Domain\Interface\VoterInterface;

final class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * @var array<string, VoterInterface>
     */
    private array $voterIndex = [];

    /**
     * @param VoterInterface[] $voters
     */
    public function __construct(array $voters = [])
    {
        foreach ($voters as $voter) {
            $this->registerVoter($voter);
        }
    }

    public function registerVoter(VoterInterface $voter): void
    {
        foreach ($voter->supportedAttributes() as $attribute) {
            $this->voterIndex[$attribute] = $voter;
        }
    }

    public function isGranted(string $attribute, int $userId, mixed $subject = null): bool
    {
        $voter = $this->voterIndex[$attribute] ?? null;

        if (null === $voter) {
            return false;
        }

        $result = $voter->vote($userId, $attribute, $subject);

        return $result->isGranted();
    }

    public function denyAccessUnlessGranted(string $attribute, int $userId, mixed $subject = null): void
    {
        if (!$this->isGranted($attribute, $userId, $subject)) {
            throw AccessDeniedException::forAttribute($attribute, $userId);
        }
    }
}
