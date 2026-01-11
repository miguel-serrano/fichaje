<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use DateTimeImmutable;

final class DateRange
{
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;

    public function __construct(DateTimeImmutable $startDate, DateTimeImmutable $endDate)
    {
        $this->validate($startDate, $endDate);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public static function fromStrings(string $startDate, string $endDate): self
    {
        return new self(
            new DateTimeImmutable($startDate),
            new DateTimeImmutable($endDate)
        );
    }

    private function validate(DateTimeImmutable $startDate, DateTimeImmutable $endDate): void
    {
        if ($endDate < $startDate) {
            throw InvalidHolidayDateRangeException::endDateBeforeStartDate();
        }

        $today = new DateTimeImmutable('today');
        if ($startDate < $today) {
            throw InvalidHolidayDateRangeException::startDateInPast();
        }
    }

    public function startDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function startDateFormatted(string $format = 'Y-m-d'): string
    {
        return $this->startDate->format($format);
    }

    public function endDateFormatted(string $format = 'Y-m-d'): string
    {
        return $this->endDate->format($format);
    }

    public function overlaps(self $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }

    public function totalDays(): int
    {
        return (int) $this->startDate->diff($this->endDate)->days + 1;
    }
}
