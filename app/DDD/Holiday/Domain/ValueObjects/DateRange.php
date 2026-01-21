<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;

final class DateRange
{
    private const SECONDS_PER_DAY = 86400;

    /**
     * Timestamp Unix de la fecha de inicio (medianoche).
     */
    private int $startDate;

    /**
     * Timestamp Unix de la fecha de fin (medianoche).
     */
    private int $endDate;

    private function __construct(
        int $startDate,
        int $endDate,
        bool $skipDateValidation = false,
    ) {
        $this->validate($startDate, $endDate, $skipDateValidation);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Crea un DateRange desde strings de fecha (Y-m-d).
     * Valida que la fecha de inicio no esté en el pasado.
     */
    public static function fromStrings(string $startDate, string $endDate): self
    {
        $start = self::parseToMidnightTimestamp($startDate);
        $end = self::parseToMidnightTimestamp($endDate);

        return new self($start, $end);
    }

    /**
     * Crea un DateRange desde timestamps Unix.
     */
    public static function fromTimestamps(int $startDate, int $endDate): self
    {
        return new self($startDate, $endDate);
    }

    /**
     * Crea un DateRange desde persistencia (timestamps Unix).
     * No valida si la fecha está en el pasado.
     */
    public static function fromPersistence(int $startDate, int $endDate): self
    {
        if ($endDate < $startDate) {
            throw InvalidHolidayDateRangeException::endDateBeforeStartDate();
        }

        return new self($startDate, $endDate, skipDateValidation: true);
    }

    /**
     * Convierte un string de fecha a timestamp de medianoche.
     */
    private static function parseToMidnightTimestamp(string $date): int
    {
        $timestamp = strtotime($date.' 00:00:00');

        if (false === $timestamp) {
            throw new \InvalidArgumentException("No se puede parsear la fecha: {$date}");
        }

        return $timestamp;
    }

    private function validate(
        int $startDate,
        int $endDate,
        bool $skipDateValidation = false,
    ): void {
        if ($endDate < $startDate) {
            throw InvalidHolidayDateRangeException::endDateBeforeStartDate();
        }

        if (!$skipDateValidation) {
            $today = strtotime('today 00:00:00');
            if ($startDate < $today) {
                throw InvalidHolidayDateRangeException::startDateInPast();
            }
        }
    }

    /**
     * Obtiene el timestamp Unix de la fecha de inicio.
     */
    public function startDate(): int
    {
        return $this->startDate;
    }

    /**
     * Obtiene el timestamp Unix de la fecha de fin.
     */
    public function endDate(): int
    {
        return $this->endDate;
    }

    /**
     * Formatea la fecha de inicio.
     */
    public function startDateFormatted(string $format = 'Y-m-d'): string
    {
        return date($format, $this->startDate);
    }

    /**
     * Formatea la fecha de fin.
     */
    public function endDateFormatted(string $format = 'Y-m-d'): string
    {
        return date($format, $this->endDate);
    }

    /**
     * Verifica si este rango se solapa con otro.
     */
    public function overlaps(self $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }

    /**
     * Calcula el total de días en el rango (inclusivo).
     */
    public function totalDays(): int
    {
        return (int) (($this->endDate - $this->startDate) / self::SECONDS_PER_DAY) + 1;
    }
}
