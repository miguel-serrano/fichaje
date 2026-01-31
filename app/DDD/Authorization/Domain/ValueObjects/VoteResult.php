<?php

declare(strict_types=1);

namespace App\DDD\Authorization\Domain\ValueObjects;

enum VoteResult: string
{
    case Granted = 'granted';
    case Denied = 'denied';
    case Abstain = 'abstain';

    public function isGranted(): bool
    {
        return self::Granted === $this;
    }

    public function isDenied(): bool
    {
        return self::Denied === $this;
    }

    public function isAbstain(): bool
    {
        return self::Abstain === $this;
    }
}
