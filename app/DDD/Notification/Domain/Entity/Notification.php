<?php

declare(strict_types=1);

namespace App\DDD\Notification\Domain\Entity;

use App\DDD\Notification\Domain\ValueObjects\Channel;
use App\DDD\Notification\Domain\ValueObjects\NotificationType;

final class Notification
{
    /**
     * @param  Channel[]  $channels
     */
    private function __construct(
        private NotificationType $type,
        private string $title,
        private string $message,
        private array $data = [],
        private array $channels = [Channel::Database],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  Channel[]  $channels
     */
    public static function create(
        NotificationType $type,
        string $title,
        string $message,
        array $data = [],
        array $channels = [Channel::Database],
    ): self {
        return new self($type, $title, $message, $data, $channels);
    }

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
