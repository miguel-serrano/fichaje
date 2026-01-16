<?php

namespace App\DDD\TimeTracking\Domain\Interface;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;
use Carbon\Carbon;

interface TimeEntryRepositoryInterface
{
    public function findById(TimeEntryId $id): ?TimeEntry;

    public function save(TimeEntry $timeEntry): TimeEntry;

    public function update(TimeEntry $timeEntry): void;

    /**
     * Find all open (unclosed) time entries from previous days.
     *
     * @return TimeEntry[]
     */
    public function findOrphanEntries(): array;

    /**
     * Get total worked seconds for a user on a specific date.
     * Optionally excludes a specific entry (useful when calculating remaining time for orphan closure).
     */
    public function getWorkedSecondsByUserAndDate(UserId $userId, Carbon $date, ?TimeEntryId $excludeEntryId = null): int;

    /**
     * Close a time entry with auto-close metadata.
     */
    public function closeWithAutoClosed(TimeEntryId $id, Carbon $closedAt, string $reason): void;
}
