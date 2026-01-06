<?php

namespace Tests\Unit\Shared\Domain\Service;

use App\DDD\Shared\Domain\Service\TimeFormatter;
use PHPUnit\Framework\TestCase;

class TimeFormatterTest extends TestCase
{
    public function test_format_time_with_zero_seconds(): void
    {
        $this->assertEquals('00:00:00', TimeFormatter::formatTime(0));
    }

    public function test_format_time_with_seconds_only(): void
    {
        $this->assertEquals('00:00:45', TimeFormatter::formatTime(45));
    }

    public function test_format_time_with_minutes_and_seconds(): void
    {
        $this->assertEquals('00:05:30', TimeFormatter::formatTime(330));
    }

    public function test_format_time_with_hours_minutes_seconds(): void
    {
        $this->assertEquals('02:30:15', TimeFormatter::formatTime(9015));
    }

    public function test_format_time_with_large_hours(): void
    {
        // 100 hours = 360000 seconds
        $this->assertEquals('100:00:00', TimeFormatter::formatTime(360000));
    }

    public function test_format_time_with_eight_hours(): void
    {
        // 8 hours = 28800 seconds
        $this->assertEquals('08:00:00', TimeFormatter::formatTime(28800));
    }

    public function test_format_month_january(): void
    {
        $this->assertEquals('Enero 2024', TimeFormatter::formatMonth('2024-01'));
    }

    public function test_format_month_december(): void
    {
        $this->assertEquals('Diciembre 2025', TimeFormatter::formatMonth('2025-12'));
    }

    public function test_format_month_july(): void
    {
        $this->assertEquals('Julio 2026', TimeFormatter::formatMonth('2026-07'));
    }
}
