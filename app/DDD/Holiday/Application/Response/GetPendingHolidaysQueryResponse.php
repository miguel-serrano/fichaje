<?php

declare(strict_types=1);

namespace App\DDD\Holiday\Application\Response;

use App\DDD\Holiday\Domain\Entity\HolidayRequest;

final class GetPendingHolidaysQueryResponse
{
    /**
     * @param HolidayRequest[] $holidays
     */
    public function __construct(
        private array $holidays,
    ) {
    }

    /**
     * @return HolidayRequest[]
     */
    public function holidays(): array
    {
        return $this->holidays;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function response(): array
    {
        return array_map(fn (HolidayRequest $holiday) => $holiday->toArray(), $this->holidays);
    }
}
