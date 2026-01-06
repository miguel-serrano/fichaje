<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain;

final class Notification
{
    /**
     * @param  Channel[]  $channels
     */
    public function __construct(
        private NotificationType $type,
        private string $title,
        private string $message,
        private array $data = [],
        private array $channels = [Channel::Database]
    ) {}

    public function type(): NotificationType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return Channel[]
     */
    public function channels(): array
    {
        return $this->channels;
    }
}
