<?php
namespace App\DDD\User\Application;
use App\DDD\User\Domain\Email;
use App\DDD\User\Domain\User;
use App\DDD\User\Domain\UserRepositoryInterface;
class CreateUserUseCase {
    public function __construct(private UserRepositoryInterface $userRepository) {}
    public function execute(string $email, string $name): User {
        $emailVO = new Email($email);
        if ($this->userRepository->existsByEmail($emailVO)) {
            throw new \InvalidArgumentException('Email already exists');
        }
        $user = User::create($emailVO, $name);
        return $this->userRepository->save($user);
    }
}