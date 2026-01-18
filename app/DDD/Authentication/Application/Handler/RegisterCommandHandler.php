<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationPolicyService;
use App\DDD\User\Domain\Specification\UniqueEmailSpecification;

final class RegisterCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHashingService $passwordHasher,
        private UniqueEmailSpecification $uniqueEmailSpec,
        private UserCreationPolicyService $creationPolicy,
    ) {
    }

    public function handle(RegisterCommand $command): User
    {
        if (!$this->uniqueEmailSpec->isSatisfiedBy($command->email)) {
            throw new UserAlreadyExistsException($command->email);
        }

        $this->creationPolicy->canCreateUser();

        $user = User::create(
            email: $command->email,
            name: $command->name,
            hashedPassword: $this->passwordHasher->hash($command->password)
        );

        return $this->userRepository->save($user);
    }
}
