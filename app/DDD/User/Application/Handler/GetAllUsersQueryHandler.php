<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\GetAllUsersQuery;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class GetAllUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(GetAllUsersQuery $query): array
    {
        $users = $this->userRepository->findAll();
        $usersArray = [];
        
        foreach ($users as $user) {
            $usersArray[] = $user->toArray();
        }
        
        return $usersArray;
    }
}
