<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DDD\RegistroHorario\UseCases\FicharEntrada;
use App\DDD\RegistroHorario\UseCases\ObtenerSegundosAcumulados;
use App\DDD\RegistroHorario\Repositories\RegistroHorarioRepositoryEloquent;
use App\DDD\RegistroHorario\Services\RegistroHorarioService;

class RegistroHorarioController extends Controller
{
    private $entradaUC;
    private $segundosUC;

    public function __construct()
    {
        $repository = new RegistroHorarioRepositoryEloquent();
        $service = new RegistroHorarioService($repository);
        $this->entradaUC = new FicharEntrada($service);
        $this->segundosUC = new ObtenerSegundosAcumulados($service);
    }

    public function fichar(Request $request)
    {
        // User puede venir de Auth o del request para testing
        $userId = Auth::id() ?: $request->get('user_id', 'a0b1435e-2e47-499e-8cf5-45017af2183c');
        $registro = $this->entradaUC->handle($userId);
        return redirect()->route('registro_horario.index');
    }

    public function index(Request $request)
    {
        $userId = Auth::id() ?: $request->get('user_id', 'a0b1435e-2e47-499e-8cf5-45017af2183c');
        $segundos = $this->segundosUC->handle($userId);
        return view('registro_horario', [ 'segundos' => $segundos ]);
    }
}

