<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;

class ToggleUserActiveCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(ToggleUserActiveCommand $command): bool
    {
        $authenticatedUserId = new UserId($command->authenticatedUserId);
        $authenticatedUser = $this->userRepository->findByIdOrFail($authenticatedUserId);

        $this->authorizationService->ensureCanToggleActive($authenticatedUser);

        $targetUserId = new UserId($command->targetUserId);
        $targetUser = $this->userRepository->findByIdOrFail($targetUserId);

        $newState = $targetUser->toggleActive();
        $this->userRepository->save($targetUser);

        return $newState;
    }
}
