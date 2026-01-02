<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\Authentication\Domain\ValueObjects\PlainPassword;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\DDD\User\Domain\Services\UserExistValidator;
use App\DDD\User\Domain\ValueObjects\Email;

final class RegisterCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserCreationValidator $userCreationValidator,
        private UserExistValidator $userExistValidator,
        private PasswordHashingService $passwordHasher,
        private AuthenticationService $authService
    ) {}

    public function handle(RegisterCommand $command): User
    {
        $emailVO = new Email($command->email);
        $plainPassword = new PlainPassword($command->password);

        $this->userExistValidator->validate($emailVO);
        $this->userCreationValidator->validate();

        $hashedPassword = $this->passwordHasher->hash($plainPassword);

        $user = User::create($emailVO, $command->name);

        $savedUser = $this->userRepository->saveWithPassword($user, $hashedPassword);

        $this->authService->login($savedUser);

        return $savedUser;
    }
}
