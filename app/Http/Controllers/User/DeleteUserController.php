<?php

namespace App\Http\Controllers\User;

use App\DDD\Shared\Domain\Bus\CommandBusInterface;
use App\DDD\User\Application\Command\DeleteUserCommand;
use App\DDD\User\Domain\Exceptions\CannotDeleteAdminUserException;
use App\DDD\User\Domain\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteUserController extends Controller
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {}

    public function __invoke(Request $request, string $id): RedirectResponse|JsonResponse
    {
        try {
            $command = new DeleteUserCommand($id);
            $this->commandBus->dispatch($command);

            return redirect()->route('users.index')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (CannotDeleteAdminUserException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 403);
            }

            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (UserNotFoundException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 404);
            }

            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}
