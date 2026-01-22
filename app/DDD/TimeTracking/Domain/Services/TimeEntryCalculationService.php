<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Domain\Services;

use App\DDD\TimeTracking\Domain\Entity\TimeEntry;

/**
 * Servicio de dominio para cálculos relacionados con entradas de tiempo.
 */
final class TimeEntryCalculationService
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * Calcula los segundos trabajados para una fecha específica.
     *
     * @param TimeEntry[] $timeEntries Lista de entradas de tiempo
     * @param string      $date        Fecha en formato Y-m-d
     */
    public function calculateAccumulatedSecondsForDate(array $timeEntries, string $date): int
    {
        $total = 0;

        foreach ($timeEntries as $entry) {
            if (date('Y-m-d', $entry->startTime()) === $date) {
                $total += $entry->workedSeconds();
            }
        }

        return $total;
    }

    /**
     * Calcula los segundos trabajados para hoy.
     *
     * @param TimeEntry[] $timeEntries Lista de entradas de tiempo
     */
    public function calculateTodayAccumulatedSeconds(array $timeEntries): int
    {
        return $this->calculateAccumulatedSecondsForDate($timeEntries, date('Y-m-d'));
    }

    /**
     * Cuenta las entradas de tiempo para una fecha específica.
     *
     * @param TimeEntry[] $timeEntries Lista de entradas de tiempo
     * @param string      $date        Fecha en formato Y-m-d
     */
    public function countEntriesForDate(array $timeEntries, string $date): int
    {
        $count = 0;

        foreach ($timeEntries as $entry) {
            if (date('Y-m-d', $entry->startTime()) === $date) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Cuenta las entradas de tiempo para hoy.
     *
     * @param TimeEntry[] $timeEntries Lista de entradas de tiempo
     */
    public function countTodayEntries(array $timeEntries): int
    {
        return $this->countEntriesForDate($timeEntries, date('Y-m-d'));
    }

    /**
     * Verifica si hay alguna entrada abierta.
     *
     * @param TimeEntry[] $timeEntries Lista de entradas de tiempo
     */
    public function hasOpenEntry(array $timeEntries): bool
    {
        foreach ($timeEntries as $entry) {
            if ($entry->isOpen()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene la entrada abierta si existe.
     *
     * @param TimeEntry[] $timeEntries Lista de entradas de tiempo
     */
    public function findOpenEntry(array $timeEntries): ?TimeEntry
    {
        foreach ($timeEntries as $entry) {
            if ($entry->isOpen()) {
                return $entry;
            }
        }

        return null;
    }
}
