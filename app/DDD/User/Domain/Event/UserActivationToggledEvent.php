<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class UserActivationToggledEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly int $userId,
        public readonly bool $isActive,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'user.activation_toggled';
    }

    /** @return array{user_id: int, is_active: bool} */
    public function toPrimitives(): array
    {
        return [
            'user_id' => $this->userId,
            'is_active' => $this->isActive,
        ];
    }
}
