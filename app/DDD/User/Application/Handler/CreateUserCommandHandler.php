<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\CreateUserCommand;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

class CreateUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(CreateUserCommand $command): User
    {
        $emailVO = new Email($command->getEmail());
        
        if ($this->userRepository->existsByEmail($emailVO)) {
            throw new \InvalidArgumentException('Email already exists');
        }
        
        $user = User::create($emailVO, $command->getName());
        return $this->userRepository->save($user);
    }
}
