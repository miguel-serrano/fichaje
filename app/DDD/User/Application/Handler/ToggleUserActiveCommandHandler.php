<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;

class ToggleUserActiveCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function handle(ToggleUserActiveCommand $command): void
    {
        $authenticatedUser = $this->userRepository->findByIdOrFail($command->authenticatedUserId);

        $this->authorizationService->assertCanToggleActive($authenticatedUser);

        $targetUser = $this->userRepository->findByIdOrFail($command->targetUserId);

        $targetUser->toggleActive();

        $this->userRepository->save($targetUser);
    }
}
