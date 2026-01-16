<?php

namespace App\Http\Controllers\Auth;

use App\DDD\Authentication\Application\Command\LogoutCommand;
use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(): RedirectResponse
    {
        $command = new LogoutCommand();
        $this->commandBus->dispatch($command);

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada correctamente.');
    }
}
