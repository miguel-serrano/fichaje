<?php

declare(strict_types=1);

namespace App\DDD\Shared\Domain\Event;

interface DomainEventSubscriber
{
    /** @return string[] */
    public static function subscribedTo(): array;
}
