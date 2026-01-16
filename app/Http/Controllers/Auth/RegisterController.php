<?php

namespace App\Http\Controllers\Auth;

use App\DDD\Authentication\Application\Command\RegisterCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
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
}
