<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;

final class DateRange
{
    private int $startDate;

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

    public static function fromStrings(string $startDate, string $endDate): self
    {
        $start = self::parseToMidnightTimestamp($startDate);
        $end = self::parseToMidnightTimestamp($endDate);

        return new self($start, $end);
    }

    public static function fromTimestamps(int $startDate, int $endDate): self
    {
        return new self($startDate, $endDate);
    }

    public static function fromPersistence(int $startDate, int $endDate): self
    {
        if ($endDate < $startDate) {
            throw InvalidHolidayDateRangeException::endDateBeforeStartDate();
        }

        return new self($startDate, $endDate, skipDateValidation: true);
    }

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

    public function startDate(): int
    {
        return $this->startDate;
    }

    public function endDate(): int
    {
        return $this->endDate;
    }

    public function startDateFormatted(string $format = 'Y-m-d'): string
    {
        return date($format, $this->startDate);
    }

    public function endDateFormatted(string $format = 'Y-m-d'): string
    {
        return date($format, $this->endDate);
    }

    public function overlaps(self $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }

    /**
     * Días naturales del rango, ambos incluidos.
     * Se cuenta por fechas de calendario: dividir segundos entre 86400 falla en los cambios de hora.
     */
    public function totalDays(): int
    {
        return self::calendarDaysBetween($this->startDate, $this->endDate);
    }

    /**
     * Días del rango que caen dentro del año indicado (p. ej. 28/12–03/01 cuenta 4 en el primer año y 3 en el siguiente).
     */
    public function daysInYear(int $year): int
    {
        $yearStart = (new \DateTimeImmutable(sprintf('%04d-01-01 00:00:00', $year)))->getTimestamp();
        $yearEnd = (new \DateTimeImmutable(sprintf('%04d-12-31 00:00:00', $year)))->getTimestamp();

        $start = max($this->startDate, $yearStart);
        $end = min($this->endDate, $yearEnd);

        if ($end < $start) {
            return 0;
        }

        return self::calendarDaysBetween($start, $end);
    }

    private static function calendarDaysBetween(int $startTimestamp, int $endTimestamp): int
    {
        $start = (new \DateTimeImmutable())->setTimestamp($startTimestamp)->setTime(0, 0);
        $end = (new \DateTimeImmutable())->setTimestamp($endTimestamp)->setTime(0, 0);

        return (int) $start->diff($end)->days + 1;
    }
}
