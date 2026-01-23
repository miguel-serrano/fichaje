<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;

final class DateRange
{
    private const SECONDS_PER_DAY = 86400;

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

    public function totalDays(): int
    {
        return (int) (($this->endDate - $this->startDate) / self::SECONDS_PER_DAY) + 1;
    }
}
