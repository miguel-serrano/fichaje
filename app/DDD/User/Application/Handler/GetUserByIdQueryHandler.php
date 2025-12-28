<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Domain\ValueObjects\UserId;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
use App\DDD\User\Domain\exceptions\UserHasNotPermissionsException;

use App\DDD\User\Application\Command\GetUserByIdQuery;

use App\DDD\User\Infrastructure\Response\UserResponse;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}


    public function handle(GetUserByIdQuery $query): UserResponse
    {
        $userId = new UserId($query->getId());
        $user = $this->userRepository->findById($userId);

        $this->validate($user, $query);

        return UserResponse::fromModel($user);
    }

    private function validate($user, $query): void
    {
        if (!$user) {
            throw new UserNotFoundException("User not found");
        }

        if ($query->getId() !== $user->id()->getValue()) {
            throw new UserHasNotPermissionsException("User has not permissions");
        }
    }
}

