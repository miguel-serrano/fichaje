<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\DDD\User\Domain\Services\UserExistValidator;

final class RegisterCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserCreationValidator $userCreationValidator,
        private UserExistValidator $userExistValidator,
        private PasswordHashingService $passwordHasher,
        private AuthenticationService $authService,
    ) {
    }

    public function handle(RegisterCommand $command): User
    {
        $this->userExistValidator->validate($command->email);

        $this->userCreationValidator->isSatisfiedBy();

        $user = User::create($command->email, $command->name);

        $savedUser = $this->userRepository->saveWithPassword(
            $user,
            $this->passwordHasher->hash($command->password)
        );

        $this->authService->login($savedUser);

        return $savedUser;
    }
}
