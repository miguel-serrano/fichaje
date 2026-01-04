<?php

namespace App\Http\Controllers\Auth;

use App\DDD\Authentication\Application\Command\LoginCommand;
use App\DDD\Authentication\Application\Command\LogoutCommand;
use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Authentication\Domain\Exceptions\InvalidCredentialsException;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthenticationController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $command = new LoginCommand($validated['email'], $validated['password']);
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

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $command = new RegisterCommand(
                $validated['name'],
                $validated['email'],
                $validated['password']
            );
            $this->commandBus->dispatch($command);

            $request->session()->regenerate();

            return redirect()->route('bienvenido')
                ->with('success', 'Cuenta creada exitosamente!');
        } catch (\Exception $e) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function logout(): RedirectResponse
    {
        $command = new LogoutCommand;
        $this->commandBus->dispatch($command);

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}
