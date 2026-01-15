<?php

namespace App\DDD\TimeTracking\Application\Response;

final class HasOpenTimeEntryQueryResponse
{
    public function __construct(
        private bool $hasOpenEntry,
    ) {
    }

    public function hasOpenEntry(): bool
    {
        return $this->hasOpenEntry;
    }

    /**
     * @return array{has_open_entry: bool}
     */
    public function response(): array
    {
        return [
            'has_open_entry' => $this->hasOpenEntry,
        ];
    }
}
