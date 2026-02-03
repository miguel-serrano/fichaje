<?php

declare(strict_types=1);

namespace App\DDD\User\Domain\Event;

use App\DDD\Shared\Domain\Event\AbstractDomainEvent;

final class UserDeletedEvent extends AbstractDomainEvent
{
    public function __construct(
        string $aggregateId,
        public readonly int $userId,
        public readonly string $email,
    ) {
        parent::__construct($aggregateId);
    }

    public static function eventName(): string
    {
        return 'user.deleted';
    }

    /** @return array{user_id: int, email: string} */
    public function toPrimitives(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
        ];
    }
}
