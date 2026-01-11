<?php

declare(strict_types=1);

namespace Tests\Unit\DDD\Holiday\Domain\ValueObjects;

use App\DDD\Holiday\Domain\Exceptions\InvalidHolidayDateRangeException;
use App\DDD\Holiday\Domain\ValueObjects\DateRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function testCanCreateValidDateRange(): void
    {
        $startDate = new DateTimeImmutable('+1 day');
        $endDate = new DateTimeImmutable('+5 days');

        $dateRange = new DateRange($startDate, $endDate);

        $this->assertEquals($startDate->format('Y-m-d'), $dateRange->startDateFormatted());
        $this->assertEquals($endDate->format('Y-m-d'), $dateRange->endDateFormatted());
    }

    public function testCanCreateFromStrings(): void
    {
        $startDate = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $endDate = (new DateTimeImmutable('+5 days'))->format('Y-m-d');

        $dateRange = DateRange::fromStrings($startDate, $endDate);

        $this->assertEquals($startDate, $dateRange->startDateFormatted());
        $this->assertEquals($endDate, $dateRange->endDateFormatted());
    }

    public function testThrowsExceptionWhenEndDateIsBeforeStartDate(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de fin debe ser posterior a la fecha de inicio');

        $startDate = new DateTimeImmutable('+5 days');
        $endDate = new DateTimeImmutable('+1 day');

        new DateRange($startDate, $endDate);
    }

    public function testThrowsExceptionWhenStartDateIsInPast(): void
    {
        $this->expectException(InvalidHolidayDateRangeException::class);
        $this->expectExceptionMessage('La fecha de inicio no puede ser anterior a hoy');

        $startDate = new DateTimeImmutable('-1 day');
        $endDate = new DateTimeImmutable('+5 days');

        new DateRange($startDate, $endDate);
    }

    public function testCalculatesTotalDaysCorrectly(): void
    {
        $startDate = new DateTimeImmutable('+1 day');
        $endDate = new DateTimeImmutable('+5 days');

        $dateRange = new DateRange($startDate, $endDate);

        $this->assertEquals(5, $dateRange->totalDays());
    }

    public function testDetectsOverlappingRanges(): void
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

    public function testDetectsNonOverlappingRanges(): void
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
