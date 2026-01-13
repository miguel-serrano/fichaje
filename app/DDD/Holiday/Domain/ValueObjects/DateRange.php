<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;

final class DateRange
{
    private \DateTimeImmutable $startDate;

    private \DateTimeImmutable $endDate;

    private function __construct(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        bool $skipDateValidation = false,
    ) {
        $this->validate($startDate, $endDate, $skipDateValidation);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public static function fromStrings(string $startDate, string $endDate): self
    {
        return new self(
            new \DateTimeImmutable($startDate),
            new \DateTimeImmutable($endDate)
        );
    }

    public static function fromPersistence(string $startDate, string $endDate): self
    {
        $start = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);

        if ($end < $start) {
            throw InvalidHolidayDateRangeException::endDateBeforeStartDate();
        }

        $instance = new self($start, $end, skipDateValidation: true);

        return $instance;
    }

    private function validate(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        bool $skipDateValidation = false,
    ): void {
        if ($endDate < $startDate) {
            throw InvalidHolidayDateRangeException::endDateBeforeStartDate();
        }

        if (!$skipDateValidation) {
            $today = new \DateTimeImmutable('today');
            if ($startDate < $today) {
                throw InvalidHolidayDateRangeException::startDateInPast();
            }
        }
    }

    public function startDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): \DateTimeImmutable
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
