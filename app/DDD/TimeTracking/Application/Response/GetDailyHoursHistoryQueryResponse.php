<?php

namespace App\DDD\TimeTracking\Application\Response;

use Illuminate\Support\Collection;

final class GetDailyHoursHistoryQueryResponse
{
    /** @var array<string, float> */
    private array $dailyHours = [];

    /**
     * @param Collection<int, \stdClass> $entries
     * @param int                        $days    0 = all history
     */
    public function __construct(Collection $entries, int $days = 0)
    {
        if (0 === $days) {
            $this->initializeFromEntries($entries);
        } else {
            $this->initializeDaysWithZero($days);
        }
        $this->processEntries($entries);
    }

    /**
     * @return array{labels: array<string>, data: array<float>, hasData: bool}
     */
    public function response(): array
    {
        ksort($this->dailyHours);

        $labels = [];
        $data = [];

        foreach ($this->dailyHours as $date => $hours) {
            $labels[] = date('d/m', strtotime($date));
            $data[] = round($hours, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'hasData' => array_sum($data) > 0,
        ];
    }

    /**
     * Initialize date range from the entries themselves (all history).
     *
     * @param Collection<int, \stdClass> $entries
     */
    private function initializeFromEntries(Collection $entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        $closedEntries = $entries->filter(fn ($e) => null !== $e->salida);
        if ($closedEntries->isEmpty()) {
            return;
        }

        $minDate = $closedEntries->min('entrada');
        $maxDate = $closedEntries->max('entrada');

        $startDate = strtotime(date('Y-m-d', $minDate));
        $endDate = strtotime(date('Y-m-d', $maxDate));

        for ($date = $startDate; $date <= $endDate; $date = strtotime('+1 day', $date)) {
            $this->dailyHours[date('Y-m-d', $date)] = 0.0;
        }
    }

    private function initializeDaysWithZero(int $days): void
    {
        $today = strtotime('today');

        for ($i = $days - 1; $i >= 0; --$i) {
            $date = date('Y-m-d', strtotime("-{$i} days", $today));
            $this->dailyHours[$date] = 0.0;
        }
    }

    /**
     * @param Collection<int, \stdClass> $entries
     */
    private function processEntries(Collection $entries): void
    {
        foreach ($entries as $entry) {
            if (null === $entry->salida) {
                continue;
            }

            $date = date('Y-m-d', $entry->entrada);

            if (!isset($this->dailyHours[$date])) {
                $this->dailyHours[$date] = 0.0;
            }

            $workedSeconds = $entry->salida - $entry->entrada;
            $workedHours = $workedSeconds / 3600;

            $this->dailyHours[$date] += $workedHours;
        }
    }
}
