<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\GetUserByIdQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetUserByIdQuery $query): User
    {
        $userId = new UserId((int) $query->getId());
        $user = $this->userRepository->findById($userId);

        $this->validate($user, $query);

        return $user;
    }

    private function validate(?User $user, GetUserByIdQuery $query): void
    {
        if (! $user) {
            throw new UserNotFoundException('User not found');
        }
    }
}
