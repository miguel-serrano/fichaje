<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class ToggleUserActiveCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService
    ) {}

    public function handle(ToggleUserActiveCommand $command): bool
    {
        $this->authorizationService->ensureCanToggleActive($command->authenticatedUser);

        $userId = new UserId($command->targetUserId);

        $user = $this->userRepository->findById($userId);
        if (! $user) {
            throw new UserNotFoundException("User {$command->targetUserId} not found");
        }

        $newState = $user->toggleActive();
        $this->userRepository->save($user);

        return $newState;
    }
}
