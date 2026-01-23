<?php

namespace App\DDD\Authentication\Application\Handler;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Services\AuthenticationService;
use App\DDD\Authentication\Domain\Services\PasswordHashingService;
use App\DDD\Authorization\Domain\Interface\RoleRepositoryInterface;
use App\DDD\Authorization\Domain\ValueObjects\RoleSlug;
use App\DDD\User\Domain\Entity\User;
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
    ) {
    }

    public function handle(RegisterCommand $command): User
    {
        // 1. Validar políticas de creación (email único, límites)
        $this->creationPolicy->canCreateUser($command->email);

        // 2. Crear entidad de dominio con password hasheado
        $user = User::create(
            email: $command->email,
            name: $command->name,
            hashedPassword: $this->passwordHasher->hash($command->password)
        );

        // 3. Persistir usuario
        $user = $this->userRepository->save($user);

        // 4. Asignar rol de empleado por defecto
        $this->roleRepository->assignRoleToUser(
            $user->id(),
            new RoleSlug(self::DEFAULT_ROLE_SLUG)
        );

        // 5. Autenticar usuario en la sesión
        $this->authenticationService->login($user);

        return $user;
    }
}
