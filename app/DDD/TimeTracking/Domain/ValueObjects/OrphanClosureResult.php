<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\ValueObjects;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;

final class OrphanClosureResult
{
    private function __construct(
        private ?TimeEntryId $entryId,
        private int $startTime,
        private int $closeTime,
        private string $reason,
    ) {
    }

    public static function forEntry(TimeEntry $entry, int $closeTime, string $reason): self
    {
        return new self(
            $entry->id(),
            $entry->startTime(),
            $closeTime,
            $reason
        );
    }

    public function entryId(): ?TimeEntryId
    {
        return $this->entryId;
    }

    public function startTime(): int
    {
        return $this->startTime;
    }

    public function closeTime(): int
    {
        return $this->closeTime;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function startTimeFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $this->startTime);
    }

    public function closeTimeFormatted(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $this->closeTime);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'entry_id' => $this->entryId?->value(),
            'entrada' => $this->startTimeFormatted(),
            'salida' => $this->closeTimeFormatted(),
            'reason' => $this->reason,
        ];
    }
}
