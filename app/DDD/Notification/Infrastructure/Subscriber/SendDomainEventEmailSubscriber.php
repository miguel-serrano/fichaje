<?php

declare(strict_types=1);

namespace App\DDD\Notification\Infrastructure\Subscriber;

use App\DDD\Administration\Domain\Event\RoleUpdatedEvent;
use App\DDD\Holiday\Domain\Event\HolidayRequestApprovedEvent;
use App\DDD\Holiday\Domain\Event\HolidayRequestCreatedEvent;
use App\DDD\Holiday\Domain\Event\HolidayRequestRejectedEvent;
use App\DDD\Shared\Domain\Event\DomainEvent;
use App\DDD\Shared\Domain\Event\DomainEventSubscriber;
use App\DDD\TimeTracking\Domain\Event\UserClockedInEvent;
use App\DDD\TimeTracking\Domain\Event\UserClockedOutEvent;
use App\DDD\User\Domain\Event\UserActivationToggledEvent;
use App\DDD\User\Domain\Event\UserCreatedEvent;
use App\DDD\User\Domain\Event\UserDeletedEvent;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Mail\DomainEventMail;
use HttpStatusCode\HttpStatus;
use Illuminate\Contracts\Mail\Mailer;
use Psr\Log\LoggerInterface;

/**
 * Envía un email a los super administradores por cada evento de dominio publicado.
 */
final class SendDomainEventEmailSubscriber implements DomainEventSubscriber
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return string[] */
    public static function subscribedTo(): array
    {
        return [
            UserCreatedEvent::eventName(),
            UserDeletedEvent::eventName(),
            UserActivationToggledEvent::eventName(),
            UserClockedInEvent::eventName(),
            UserClockedOutEvent::eventName(),
            HolidayRequestCreatedEvent::eventName(),
            HolidayRequestApprovedEvent::eventName(),
            HolidayRequestRejectedEvent::eventName(),
            RoleUpdatedEvent::eventName(),
        ];
    }

    public function __invoke(DomainEvent $event): void
    {
        $mail = DomainEventMail::fromDomainEvent($event);

        foreach ($this->userRepository->findByRole('super_admin') as $superAdmin) {
            $this->logger->info(__CLASS__, [
                'status' => HttpStatus::OK,
                'event' => $event::eventName(),
                'event_id' => $event->eventId(),
                'data' => $event->toPrimitives(),
                'recipient' => $superAdmin->email()->value(),
            ]);

            try {
                $this->mailer->to($superAdmin->email()->value())->send(clone $mail);
            } catch (\Throwable $exception) {
                $this->logger->error('No se pudo enviar el email del evento de dominio', [
                    'status' => HttpStatus::INTERNAL_SERVER_ERROR,
                    'exception' => $exception->getMessage(),
                    'class' => __CLASS__,
                    'event' => $event::eventName(),
                    'data' => $event->toPrimitives(),
                    'recipient' => $superAdmin->email()->value(),
                ]);
            }
        }
    }
}
