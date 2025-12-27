<?php

namespace App\Http\Controllers\RegistroHorario;

use Illuminate\Http\Request;
use App\DDD\RegistroHorario\Application\FicharEntrada;
use App\DDD\RegistroHorario\Application\FicharSalida;
use App\DDD\RegistroHorario\Application\ObtenerSegundosAcumulados;
use App\DDD\RegistroHorario\Infrastructure\Persistence\Eloquent\RegistroHorarioRepositoryEloquent;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;
use App\DDD\User\Application\Command\GetAllUsersWithTimeQuery;
use App\DDD\User\Application\Handler\GetAllUsersWithTimeQueryHandler;
use App\Http\Controllers\Controller;

class RegistroHorarioController extends Controller
{
    private $entradaUC;
    private $salidaUC;
    private $segundosUC;
    private $service;

    public function __construct(
        private GetAllUsersWithTimeQueryHandler $getAllUsersWithTimeHandler
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
        $users = $this->getAllUsersWithTimeHandler->handle(new GetAllUsersWithTimeQuery());
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

