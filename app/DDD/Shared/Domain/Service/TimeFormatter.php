<?php

namespace App\DDD\Shared\Domain\Service;

class TimeFormatter
{
    private const MONTHS = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ];

    public static function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT).':'.
               str_pad((string) $minutes, 2, '0', STR_PAD_LEFT).':'.
               str_pad((string) $remainingSeconds, 2, '0', STR_PAD_LEFT);
    }

    public static function formatMonth(string $yearMonth): string
    {
        [$year, $month] = explode('-', $yearMonth);

        return self::MONTHS[$month].' '.$year;
    }
}
