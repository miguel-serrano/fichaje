<?php

namespace App\DDD\User\Application\Handler;

use App\DDD\User\Application\Command\AcceptTermsCommand;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;

/**
 * El usuario autenticado acepta los términos de uso.
 * Solo actúa sobre sí mismo (el controlador pasa el id autenticado), por eso no pide permiso.
 */
final class AcceptTermsCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function handle(AcceptTermsCommand $command): void
    {
        $user = $this->userRepository->findByIdOrFail($command->userId);

        if ($user->hasAcceptedTerms()) {
            return;
        }

        $user->acceptTerms();

        $this->userRepository->save($user);
    }
}
