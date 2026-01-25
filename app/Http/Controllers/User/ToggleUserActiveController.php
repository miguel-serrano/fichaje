<?php

namespace App\Http\Controllers\User;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Application\Command\ToggleUserActiveCommand;
use App\DDD\User\Domain\Exceptions\UnauthorizedException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ToggleUserActiveController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(string $id): RedirectResponse
    {
        try {
            $user = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

            $isActive = $this->commandBus->dispatch(
                ToggleUserActiveCommand::create(
                    authenticatedUserId: $user->id()->value(),
                    targetUserId: (int) $id,
                )
            );

            $message = $isActive ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';

            return redirect()->route('users.index')->with('success', $message);
        } catch (UserNotFoundException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (UnauthorizedException $e) {
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('users.index')
                ->with('error', 'Error al cambiar el estado del usuario');
        }
    }
}
