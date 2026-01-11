<?php

namespace App\DDD\TimeTracking\Domain;

use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use Carbon\Carbon;

final class TimeEntry
{
    private ?TimeEntryId $id;

    private UserId $userId;

    private \DateTime $startTime;

    private ?\DateTime $endTime;

    private bool $autoClosed;

    private ?string $autoCloseReason;

    private function __construct(
        ?TimeEntryId $id,
        UserId $userId,
        \DateTime $startTime,
        ?\DateTime $endTime,
        bool $autoClosed = false,
        ?string $autoCloseReason = null,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->autoClosed = $autoClosed;
        $this->autoCloseReason = $autoCloseReason;
    }

    public static function create(UserId $userId): self
    {
        return new self(
            null,
            $userId,
            Carbon::now()->toDateTime(),
            null,
            false,
            null
        );
    }

    public static function fromPrimitives(
        ?int $id,
        int $userId,
        string $startTime,
        ?string $endTime,
        bool $autoClosed = false,
        ?string $autoCloseReason = null,
    ): self {
        return new self(
            $id ? new TimeEntryId($id) : null,
            new UserId($userId),
            new \DateTime($startTime),
            $endTime ? new \DateTime($endTime) : null,
            $autoClosed,
            $autoCloseReason
        );
    }

    public function id(): ?TimeEntryId
    {
        return $this->id;
    }

    public function setId(TimeEntryId $id): void
    {
        $this->id = $id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function startTime(): \DateTime
    {
        return $this->startTime;
    }

    public function endTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function close(): void
    {
        $this->endTime = Carbon::now()->toDateTime();
    }

    public function closeAt(\DateTime $closeTime, bool $autoClosed = false, ?string $autoCloseReason = null): void
    {
        $this->endTime = $closeTime;
        $this->autoClosed = $autoClosed;
        $this->autoCloseReason = $autoCloseReason;
    }

    public function isOpen(): bool
    {
        return null === $this->endTime;
    }

    public function isAutoClosed(): bool
    {
        return $this->autoClosed;
    }

    public function autoCloseReason(): ?string
    {
        return $this->autoCloseReason;
    }

    public function workedSeconds(): int
    {
        if ($this->startTime && $this->endTime) {
            return $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
        }

        // If open, calculate with current time (theoretical time)
        if ($this->startTime && null === $this->endTime) {
            return Carbon::now()->getTimestamp() - $this->startTime->getTimestamp();
        }

        return 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id ? $this->id->value() : null,
            'user_id' => $this->userId->value(),
            'entrada' => $this->startTime->format('Y-m-d H:i:s'),
            'salida' => $this->endTime ? $this->endTime->format('Y-m-d H:i:s') : null,
            'auto_closed' => $this->autoClosed,
            'auto_close_reason' => $this->autoCloseReason,
        ];
    }
}
