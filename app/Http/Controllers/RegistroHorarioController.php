<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DDD\RegistroHorario\UseCases\FicharEntrada;
use App\DDD\RegistroHorario\UseCases\FicharSalida;
use App\DDD\RegistroHorario\UseCases\ObtenerSegundosAcumulados;
use App\DDD\RegistroHorario\Repositories\RegistroHorarioRepositoryEloquent;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\User\Application\GetAllUsersUseCase;

class RegistroHorarioController extends Controller
{
    private $entradaUC;
    private $salidaUC;
    private $segundosUC;
    private $service;

    public function __construct(
        private GetAllUsersUseCase $getAllUsersUseCase
    )
    {
        $repository = new RegistroHorarioRepositoryEloquent();
        $this->service = new RegistroHorarioService($repository);
        $this->entradaUC = new FicharEntrada($this->service);
        $this->salidaUC = new FicharSalida($this->service);
        $this->segundosUC = new ObtenerSegundosAcumulados($this->service);
    }

    public function ficharEntrada(Request $request)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $this->entradaUC->handle($validated['userUuid']);
            return redirect()->route('registro_horario.index', ['userUuid' => $validated['userUuid']])
                ->with('success', 'Entrada registrada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('registro_horario.index', ['userUuid' => $validated['userUuid']])
                ->with('error', $e->getMessage());
        }
    }

    public function ficharSalida(Request $request)
    {
        $validated = $request->validate([
            'userUuid' => 'required|string|uuid'
        ]);

        try {
            $this->salidaUC->handle($validated['userUuid']);
            return redirect()->route('registro_horario.index', ['userUuid' => $validated['userUuid']])
                ->with('success', 'Salida registrada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('registro_horario.index', ['userUuid' => $validated['userUuid']])
                ->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $users = $this->getAllUsersUseCase->execute();
        $userUuid = $request->input('userUuid');
        $segundos = 0;
        $tieneRegistroAbierto = false;

        if ($userUuid) {
            $segundos = $this->segundosUC->handle($userUuid);
            $ultimoRegistro = $this->service->obtenerUltimoRegistro($userUuid);
            $tieneRegistroAbierto = $ultimoRegistro !== null;
        }

        return view('registro_horario', [
            'users' => $users,
            'segundos' => $segundos,
            'selectedUserUuid' => $userUuid,
            'tieneRegistroAbierto' => $tieneRegistroAbierto
        ]);
    }
}

