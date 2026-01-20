<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationPolicyService;

final class RegisterCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHashingService $passwordHasher,
        private UserCreationPolicyService $creationPolicy,
    ) {
    }

    public function handle(RegisterCommand $command): User
    {
        $this->creationPolicy->canCreateUser($command->email);

        $user = User::create(
            email: $command->email,
            name: $command->name,
            hashedPassword: $this->passwordHasher->hash($command->password)
        );

        return $this->userRepository->save($user);
    }
}
