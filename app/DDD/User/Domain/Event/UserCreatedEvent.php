<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class UserCreatedEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly int $userId,
        public readonly string $email,
        public readonly string $name,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'user.created';
    }

    /** @return array{user_id: int, email: string, name: string} */
    public function toPrimitives(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'name' => $this->name,
        ];
    }
}
