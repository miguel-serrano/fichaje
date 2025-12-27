<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DDD\RegistroHorario\UseCases\FicharEntrada;
use App\DDD\RegistroHorario\UseCases\ObtenerSegundosAcumulados;
use App\DDD\RegistroHorario\Repositories\RegistroHorarioRepositoryEloquent;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\User\Application\GetAllUsersUseCase;

class RegistroHorarioController extends Controller
{
    private $entradaUC;
    private $segundosUC;

    public function __construct(
        private GetAllUsersUseCase $getAllUsersUseCase
    )
    {
        $repository = new RegistroHorarioRepositoryEloquent();
        $service = new RegistroHorarioService($repository);
        $this->entradaUC = new FicharEntrada($service);
        $this->segundosUC = new ObtenerSegundosAcumulados($service);
    }

    public function fichar(Request $request)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        $registro = $this->entradaUC->handle($validated['userUuid']);
        return redirect()->route('registro_horario.index')->with('success', 'Registro guardado correctamente');
    }

    public function index(Request $request)
    {
        $users = $this->getAllUsersUseCase->execute();
        $userUuid = $request->input('userUuid');
        $segundos = 0;

        if ($userUuid) {
            $segundos = $this->segundosUC->handle($userUuid);
        }

        return view('registro_horario', [
            'users' => $users,
            'segundos' => $segundos,
            'selectedUserUuid' => $userUuid
        ]);
    }
}

