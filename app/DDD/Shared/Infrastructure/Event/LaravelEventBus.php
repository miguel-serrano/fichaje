<?php

declare(strict_types=1);

namespace App\DDD\Shared\Infrastructure\Event;

use App\DDD\Shared\Domain\Event\DomainEvent;
use App\DDD\Shared\Domain\Event\DomainEventRepositoryInterface;
use App\DDD\Shared\Domain\Event\DomainEventSubscriber;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use Illuminate\Contracts\Container\Container;

final class LaravelEventBus implements EventBusInterface
{
    /** @var array<string, class-string<DomainEventSubscriber>[]> */
    private array $subscribers = [];

    public function __construct(
        private readonly DomainEventRepositoryInterface $eventRepository,
        private readonly Container $container,
    ) {
    }

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->eventRepository->save($event);
            $this->dispatch($event);
        }
    }

    private function dispatch(DomainEvent $event): void
    {
        $eventName = $event::eventName();

        if (!isset($this->subscribers[$eventName])) {
            return;
        }

        foreach ($this->subscribers[$eventName] as $subscriberClass) {
            $subscriber = $this->container->make($subscriberClass);
            $subscriber($event);
        }
    }

    /**
     * @param class-string<DomainEventSubscriber> $subscriberClass
     */
    public function subscribe(string $subscriberClass): void
    {
        $eventNames = $subscriberClass::subscribedTo();

        foreach ($eventNames as $eventName) {
            $this->subscribers[$eventName][] = $subscriberClass;
        }
    }
}
