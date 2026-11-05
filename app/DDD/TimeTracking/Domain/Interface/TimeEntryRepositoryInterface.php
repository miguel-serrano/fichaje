<?php

namespace App\DDD\TimeTracking\Domain\Interface;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;
use App\DDD\TimeTracking\Domain\ValueObjects\TimeEntryId;
use App\DDD\User\Domain\ValueObjects\UserId;

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
     *
     * @param UserId           $userId         ID del usuario
     * @param int              $dateTimestamp  Timestamp Unix de la fecha
     * @param TimeEntryId|null $excludeEntryId ID de entrada a excluir (opcional)
     */
    public function getWorkedSecondsByUserAndDate(UserId $userId, int $dateTimestamp, ?TimeEntryId $excludeEntryId = null): int;

    /**
     * Segundos trabajados en fichajes cerrados cuya entrada está en [from, to).
     * Los fichajes abiertos no cuentan.
     */
    public function getClosedWorkedSecondsBetween(UserId $userId, int $fromTimestamp, int $toTimestamp): int;

    /**
     * "Ventana de fichaje": entradas abiertas (de cualquier día) + entradas que empezaron hoy.
     * Es todo lo que necesitan los validadores de fichaje y el cálculo de segundos de hoy.
     *
     * @return TimeEntry[]
     */
    public function findClockingWindowByUserId(UserId $userId): array;

    /**
     * Fichajes del usuario cuya entrada está en [from, to), ordenados por entrada.
     *
     * @return TimeEntry[]
     */
    public function findByUserIdBetween(UserId $userId, int $fromTimestamp, int $toTimestamp): array;

    /**
     * Close a time entry with auto-close metadata.
     *
     * @param TimeEntryId $id       ID de la entrada
     * @param int         $closedAt Timestamp Unix del momento de cierre
     * @param string      $reason   Razón del auto-cierre
     */
    public function closeWithAutoClosed(TimeEntryId $id, int $closedAt, string $reason): void;

    /**
     * Find time entries for a user within a date range.
     *
     * @param UserId $userId ID del usuario
     * @param int    $days   Número de días hacia atrás desde hoy
     *
     * @return \Illuminate\Support\Collection<int, \stdClass>
     */
    public function findByUserIdInDateRange(UserId $userId, int $days = 30): \Illuminate\Support\Collection;
}
