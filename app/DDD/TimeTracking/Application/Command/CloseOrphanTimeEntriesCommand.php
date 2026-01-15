<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Command;

final class CloseOrphanTimeEntriesCommand
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }
}
