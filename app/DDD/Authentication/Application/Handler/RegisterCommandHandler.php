<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Administration\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Administration\Domain\ValueObjects\RoleSlug;
use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Event\UserCreatedEvent;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserCreationPolicyService;

final class RegisterCommandHandler
{
    private const DEFAULT_ROLE_SLUG = 'employee';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private RoleRepositoryInterface $roleRepository,
        private PasswordHashingService $passwordHasher,
        private UserCreationPolicyService $creationPolicy,
        private AuthenticationService $authenticationService,
        private EventBusInterface $eventBus,
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

        $user = $this->userRepository->save($user);

        $this->eventBus->publish(new UserCreatedEvent(
            $user->uuid()->value(),
            $user->id()->value(),
            $user->email()->value(),
            $user->name()->value(),
        ));

        $this->roleRepository->assignRoleToUserBySystem(
            $user->id(),
            new RoleSlug(self::DEFAULT_ROLE_SLUG)
        );

        $this->authenticationService->login($user);

        return $user;
    }
}
