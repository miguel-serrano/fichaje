<?php

declare(strict_types=1);

namespace App\DDD\TimeTracking\Application\Response;

final class CloseOrphanTimeEntriesCommandResponse
{
    /**
     * @param array<int, array<int, array<string, mixed>>> $closedByUser
     */
    public function __construct(
        private array $closedByUser,
    ) {
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function closedByUser(): array
    {
        return $this->closedByUser;
    }

    public function totalClosed(): int
    {
        $total = 0;
        foreach ($this->closedByUser as $entries) {
            $total += count($entries);
        }

        return $total;
    }

    public function usersAffected(): int
    {
        return count($this->closedByUser);
    }

    /**
     * @return array{closed_by_user: array<int, array<int, array<string, mixed>>>, total_closed: int, users_affected: int}
     */
    public function response(): array
    {
        return [
            'closed_by_user' => $this->closedByUser,
            'total_closed' => $this->totalClosed(),
            'users_affected' => $this->usersAffected(),
        ];
    }
}
