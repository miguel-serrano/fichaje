<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Query\GetUserByIdQuery;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\Services\UserAuthorizationServiceInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\User as EloquentUser;

class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserAuthorizationServiceInterface $authorizationService
    ) {}

    public function handle(GetUserByIdQuery $query): User
    {
        $userId = new UserId($query->targetUserId);
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new UserNotFoundException('User not found');
        }

        // Authorization check
        $targetEloquentUser = EloquentUser::query()->find($query->targetUserId);
        $this->authorizationService->ensureCanView(
            $query->authenticatedUser,
            $targetEloquentUser
        );

        return $user;
    }
}
