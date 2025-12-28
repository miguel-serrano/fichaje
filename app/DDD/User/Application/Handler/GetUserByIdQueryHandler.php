<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use App\DDD\User\Infrastructure\Response\UserResponse;
use App\DDD\User\Application\Command\GetUserByIdQuery;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}


    public function handle(GetUserByIdQuery $query): UserResponse
    {
        $userId = new UserId($query->getId());
        $user = $this->userRepository->findById($userId);

        $this->guardUserFound($user, $query->getId());

        return UserResponse::fromModel($user);
    }

    private function guardUserFound($user, $id): void
    {
        if (!$user) {
            throw new UserNotFoundException("User {$id} not found");
        }
    }
}

