<?php

namespace App\DDD\TimeTracking\Application\Response;

use App\DDD\Shared\Domain\Service\TimeFormatter;

final class GetAccumulatedSecondsQueryResponse
{
    public function __construct(
        private int $seconds,
    ) {
    }

    public function seconds(): int
    {
        return $this->seconds;
    }

    /**
     * @return array{seconds: int, formatted: string}
     */
    public function response(): array
    {
        return [
            'seconds' => $this->seconds,
            'formatted' => TimeFormatter::formatTime($this->seconds),
        ];
    }
}
