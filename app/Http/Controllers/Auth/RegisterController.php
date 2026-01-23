<?php

namespace App\Http\Controllers\Auth;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Domain\Exceptions\MaxUsersLimitExceededException;
use App\DDD\User\Domain\Exceptions\UserAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(RegisterRequest $request): RedirectResponse
    {
        try {
            $this->commandBus->dispatch(
                RegisterCommand::create(
                    $request->validated('name'),
                    $request->validated('email'),
                    $request->validated('password')
                )
            );

            $request->session()->regenerate();

            return redirect()->route('bienvenido')
                ->with('success', 'Cuenta creada exitosamente!');
        } catch (UserAlreadyExistsException $e) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => $e->getMessage()]);
        } catch (MaxUsersLimitExceededException $e) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }
}
