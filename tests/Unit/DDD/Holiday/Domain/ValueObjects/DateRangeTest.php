<?php

declare(strict_types=1);

namespace Tests\Unit\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function testCanCreateValidDateRange(): void
    {
        $startDate = (new \DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new \DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals($startDate, $dateRange->startDateFormatted());
        $this->assertEquals($endDate, $dateRange->endDateFormatted());
    }

    public function testCanCreateFromStrings(): void
    {
        $startDate = (new \DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new \DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals($startDate, $dateRange->startDateFormatted());
        $this->assertEquals($endDate, $dateRange->endDateFormatted());
    }

    public function testThrowsExceptionWhenEndDateIsBeforeStartDate(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');

        $startDate = (new \DateTimeImmutable('+5 days'))->format('Y-m-d');
        $endDate = (new \DateTimeImmutable('+1 day'))->format('Y-m-d');

        DateRange::fromStrings($startDate, $endDate);
    }

    public function testThrowsExceptionWhenStartDateIsInPast(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de inicio no puede ser anterior a hoy');

        $startDate = (new \DateTimeImmutable('-1 day'))->format('Y-m-d');
        $endDate = (new \DateTimeImmutable('+5 days'))->format('Y-m-d');

        DateRange::fromStrings($startDate, $endDate);
    }

    public function testCalculatesTotalDaysCorrectly(): void
    {
        $startDate = (new \DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new \DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals(5, $dateRange->totalDays());
    }

    public function testFromPersistenceAllowsPastDates(): void
    {
        $startDate = strtotime('-30 days 00:00:00');
        $endDate = strtotime('-25 days 00:00:00');

        $dateRange = DateRange::fromPersistence($startDate, $endDate);

        $this->assertEquals(date('Y-m-d', $startDate), $dateRange->startDateFormatted());
        $this->assertEquals(date('Y-m-d', $endDate), $dateRange->endDateFormatted());
    }

    public function testFromPersistenceStillValidatesEndDateBeforeStartDate(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');

        $startDate = strtotime('-20 days 00:00:00');
        $endDate = strtotime('-25 days 00:00:00');

        DateRange::fromPersistence($startDate, $endDate);
    }

    public function testDetectsOverlappingRanges(): void
    {
        $range1 = DateRange::fromStrings(
            (new \DateTimeImmutable('+1 day'))->format('Y-m-d'),
            (new \DateTimeImmutable('+10 days'))->format('Y-m-d')
        );

        $range2 = DateRange::fromStrings(
            (new \DateTimeImmutable('+5 days'))->format('Y-m-d'),
            (new \DateTimeImmutable('+15 days'))->format('Y-m-d')
        );

        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    public function testDetectsNonOverlappingRanges(): void
    {
        $range1 = DateRange::fromStrings(
            (new \DateTimeImmutable('+1 day'))->format('Y-m-d'),
            (new \DateTimeImmutable('+5 days'))->format('Y-m-d')
        );

        $range2 = DateRange::fromStrings(
            (new \DateTimeImmutable('+10 days'))->format('Y-m-d'),
            (new \DateTimeImmutable('+15 days'))->format('Y-m-d')
        );

        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }

    public function testTotalDaysIsCorrectAcrossSpringDaylightSavingChange(): void
    {
        $this->withTimezone('Europe/Madrid', function (): void {
            $range = DateRange::fromPersistence(
                (new \DateTimeImmutable('2027-03-28 00:00:00'))->getTimestamp(),
                (new \DateTimeImmutable('2027-03-31 00:00:00'))->getTimestamp(),
            );

            $this->assertSame(4, $range->totalDays());
        });
    }

    public function testTotalDaysIsCorrectAcrossAutumnDaylightSavingChange(): void
    {
        $this->withTimezone('Europe/Madrid', function (): void {
            $range = DateRange::fromPersistence(
                (new \DateTimeImmutable('2027-10-30 00:00:00'))->getTimestamp(),
                (new \DateTimeImmutable('2027-11-01 00:00:00'))->getTimestamp(),
            );

            $this->assertSame(3, $range->totalDays());
        });
    }

    public function testDaysInYearSplitsARangeThatCrossesNewYear(): void
    {
        $this->withTimezone('Europe/Madrid', function (): void {
            $range = DateRange::fromPersistence(
                (new \DateTimeImmutable('2026-12-28 00:00:00'))->getTimestamp(),
                (new \DateTimeImmutable('2027-01-03 00:00:00'))->getTimestamp(),
            );

            $this->assertSame(7, $range->totalDays());
            $this->assertSame(4, $range->daysInYear(2026));
            $this->assertSame(3, $range->daysInYear(2027));
        });
    }

    public function testDaysInYearIsZeroForARangeOutsideTheYear(): void
    {
        $range = DateRange::fromPersistence(
            (new \DateTimeImmutable('2026-05-04 00:00:00'))->getTimestamp(),
            (new \DateTimeImmutable('2026-05-08 00:00:00'))->getTimestamp(),
        );

        $this->assertSame(0, $range->daysInYear(2025));
        $this->assertSame(5, $range->daysInYear(2026));
    }

    public function testDaysInYearCountsASingleDayOnTheLastDayOfTheYear(): void
    {
        $range = DateRange::fromPersistence(
            (new \DateTimeImmutable('2026-12-31 00:00:00'))->getTimestamp(),
            (new \DateTimeImmutable('2026-12-31 00:00:00'))->getTimestamp(),
        );

        $this->assertSame(1, $range->daysInYear(2026));
        $this->assertSame(0, $range->daysInYear(2027));
    }

    private function withTimezone(string $timezone, callable $assertions): void
    {
        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set($timezone);

        try {
            $assertions();
        } finally {
            date_default_timezone_set($previousTimezone);
        }
    }
}
