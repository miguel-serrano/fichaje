<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationValidator;
use App\DDD\User\Domain\Services\UserExistValidator;
use App\DDD\User\Domain\ValueObjects\Email;

class CreateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserCreationValidator $userCreationValidator,
        private UserExistValidator $userExistValidator
    ) {}

    public function handle(CreateUserCommand $command): User
    {
        $emailVO = new Email($command->getEmail());

        // Domain validation: Check if email already exists
        $this->userExistValidator->validate($emailVO);

        // Domain validation: Check if user creation is allowed (max users limit)
        $this->userCreationValidator->validate();

        $user = User::create($emailVO, $command->getName());

        return $this->userRepository->save($user);
    }
}
