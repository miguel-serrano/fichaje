<?php

namespace App\Http\Controllers\Auth;

use App\DDD\Authentication\Application\Command\LoginCommand;
use App\DDD\Authentication\Domain\Exceptions\InvalidCredentialsException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $command = LoginCommand::create($validated['email'], $validated['password']);
            $this->commandBus->dispatch($command);

            $request->session()->regenerate();

            return redirect()->intended(route('registro_horario.index'))
                ->with('success', 'Bienvenido de nuevo!');
        } catch (InvalidCredentialsException $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }
}
