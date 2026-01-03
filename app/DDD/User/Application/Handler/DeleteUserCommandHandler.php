<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Exceptions\CannotDeleteAdminUserException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Models\User as EloquentUser;

class DeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(DeleteUserCommand $command): void
    {
        $userId = new UserId($command->getId());

        // Verify user exists
        $user = $this->userRepository->findById($userId);
        if (! $user) {
            throw new UserNotFoundException("User {$command->getId()} not found");
        }

        // Verify user is not an admin
        $eloquentUser = EloquentUser::query()->find($command->getId());
        if ($eloquentUser && $eloquentUser->remember_token === 'soyAdm1n') {
            throw new CannotDeleteAdminUserException('No se puede eliminar un usuario administrador');
        }

        // Delete user
        $deleted = $this->userRepository->delete($userId);
        if (! $deleted) {
            throw new \RuntimeException("Failed to delete user {$command->getId()}");
        }
    }
}
