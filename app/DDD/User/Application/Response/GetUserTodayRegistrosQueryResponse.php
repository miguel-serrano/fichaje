<?php

namespace App\DDD\User\Application\Response;

use App\DDD\TimeTracking\Domain\TimeEntry;

class GetUserTodayRegistrosQueryResponse
{
    /** @var TimeEntry[] */
    private array $entries;

    /**
     * @param array<int, array{id: int, user_id: int, entrada: string, salida: ?string, auto_closed?: bool, auto_close_reason?: ?string}> $rawEntries
     */
    public function __construct(array $rawEntries)
    {
        $this->entries = array_map(
            fn (array $r) => TimeEntry::fromPrimitives(
                $r['id'],
                $r['user_id'],
                $r['entrada'],
                $r['salida'],
                $r['auto_closed'] ?? false,
                $r['auto_close_reason'] ?? null
            ),
            $rawEntries
        );
    }

    /**
     * @return TimeEntry[]
     */
    public function response(): array
    {
        return $this->entries;
    }
}
