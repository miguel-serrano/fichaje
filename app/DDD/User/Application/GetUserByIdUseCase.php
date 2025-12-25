<?php
namespace App\DDD\User\Application;
use App\DDD\User\Domain\UserId;
use App\DDD\User\Domain\UserRepositoryInterface;
use App\DDD\User\Domain\exceptions\UserNotFoundException;
class GetUserByIdUseCase {
    public function __construct(private UserRepositoryInterface $userRepository) {}
    public function execute(string $id): array {
        $userId = new UserId($id);
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UserNotFoundException("User {$id} not found");
        }
        return $user->toArray();
    }
}