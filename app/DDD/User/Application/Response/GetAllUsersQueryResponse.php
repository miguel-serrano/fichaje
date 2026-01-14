<?php

namespace App\DDD\User\Application\Response;

use App\DDD\User\Domain\Entity\User;

class GetAllUsersQueryResponse
{
    /**
     * @param User[] $users
     */
    public function __construct(
        private array $users,
    ) {
    }

    /**
     * @return User[]
     */
    public function users(): array
    {
        return $this->users;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function response(): array
    {
        return array_map(fn (User $user) => $user->toArray(), $this->users);
    }
}
