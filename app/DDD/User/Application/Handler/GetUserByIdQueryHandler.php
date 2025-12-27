<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetUserByIdQuery $query): array
    {
        $userId = new UserId($query->getId());
        $user = $this->userRepository->findById($userId);
        
        if (!$user) {
            throw new UserNotFoundException("User {$query->getId()} not found");
        }
        
        return $user->toArray();
    }
}
