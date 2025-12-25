<?php

namespace App\DDD\User\Application;

use App\DDD\User\Domain\UserRepositoryInterface;

class GetAllUsersUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function execute(): array
    {
        $users = $this->userRepository->findAll();
    
        return array_map(fn($user) => $user->toArray(), $users);
    }
}

