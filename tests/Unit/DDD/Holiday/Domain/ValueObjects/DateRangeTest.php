<?php

declare(strict_types=1);

namespace Tests\Unit\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function test_can_create_valid_date_range(): void
    {
        $startDate = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals($startDate, $dateRange->startDateFormatted());
        $this->assertEquals($endDate, $dateRange->endDateFormatted());
    }

    public function test_can_create_from_strings(): void
    {
        $startDate = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals($startDate, $dateRange->startDateFormatted());
        $this->assertEquals($endDate, $dateRange->endDateFormatted());
    }

    public function test_throws_exception_when_end_date_is_before_start_date(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');

        $startDate = (new DateTimeImmutable('+5 days'))->format('Y-m-d');
        $endDate = (new DateTimeImmutable('+1 day'))->format('Y-m-d');

        DateRange::fromStrings($startDate, $endDate);
    }

    public function test_throws_exception_when_start_date_is_in_past(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de inicio no puede ser anterior a hoy');

        $startDate = (new DateTimeImmutable('-1 day'))->format('Y-m-d');
        $endDate = (new DateTimeImmutable('+5 days'))->format('Y-m-d');

        DateRange::fromStrings($startDate, $endDate);
    }

    public function test_calculates_total_days_correctly(): void
    {
        $startDate = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals(5, $dateRange->totalDays());
    }

    public function test_from_persistence_allows_past_dates(): void
    {
        $startDate = strtotime('-30 days 00:00:00');
        $endDate = strtotime('-25 days 00:00:00');

        $dateRange = DateRange::fromPersistence($startDate, $endDate);

        $this->assertEquals(date('Y-m-d', $startDate), $dateRange->startDateFormatted());
        $this->assertEquals(date('Y-m-d', $endDate), $dateRange->endDateFormatted());
    }

    public function test_from_persistence_still_validates_end_date_before_start_date(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');

        $startDate = strtotime('-20 days 00:00:00');
        $endDate = strtotime('-25 days 00:00:00');

        DateRange::fromPersistence($startDate, $endDate);
    }

    public function test_detects_overlapping_ranges(): void
    {
        $range1 = DateRange::fromStrings(
            (new DateTimeImmutable('+1 day'))->format('Y-m-d'),
            (new DateTimeImmutable('+10 days'))->format('Y-m-d')
        );

        $range2 = DateRange::fromStrings(
            (new DateTimeImmutable('+5 days'))->format('Y-m-d'),
            (new DateTimeImmutable('+15 days'))->format('Y-m-d')
        );

        $this->assertTrue($range1->overlaps($range2));
        $this->assertTrue($range2->overlaps($range1));
    }

    public function test_detects_non_overlapping_ranges(): void
    {
        $range1 = DateRange::fromStrings(
            (new DateTimeImmutable('+1 day'))->format('Y-m-d'),
            (new DateTimeImmutable('+5 days'))->format('Y-m-d')
        );

        $range2 = DateRange::fromStrings(
            (new DateTimeImmutable('+10 days'))->format('Y-m-d'),
            (new DateTimeImmutable('+15 days'))->format('Y-m-d')
        );

        $this->assertFalse($range1->overlaps($range2));
        $this->assertFalse($range2->overlaps($range1));
    }
}
