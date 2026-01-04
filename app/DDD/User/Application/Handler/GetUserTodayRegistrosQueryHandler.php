<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetUserTodayRegistrosQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetUserTodayRegistrosQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /** @return array<array-key, mixed> */
    public function handle(GetUserTodayRegistrosQuery $query): array
    {
        $userId = new UserId($query->getUserId());

        return $this->userRepository->findTodayRegistrosByUserId($userId);
    }
}
