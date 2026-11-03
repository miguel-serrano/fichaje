<?php

declare(strict_types=1);

namespace App\Mail;

use App\DDD\Shared\Domain\Event\DomainEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class DomainEventMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, string>  $fields
     */
    public function __construct(
        public readonly string $eventName,
        public readonly string $aggregateId,
        public readonly string $occurredOn,
        public readonly array $fields,
    ) {}

    public static function fromDomainEvent(DomainEvent $event): self
    {
        $fields = [];
        foreach ($event->toPrimitives() as $key => $value) {
            $fields[$key] = is_string($value) ? $value : (string) json_encode($value);
        }

        return new self(
            $event::eventName(),
            $event->aggregateId(),
            $event->occurredOn()->format('d/m/Y H:i:s'),
            $fields,
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('[%s] Evento %s', config('app.name'), $this->eventName),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.domain-event',
        );
    }
}
