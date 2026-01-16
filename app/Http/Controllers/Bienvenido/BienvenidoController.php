<?php

namespace App\Http\Controllers\Bienvenido;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BienvenidoController extends Controller
{
    public function index(): View
    {
        return view('bienvenida');
    }

    public function acceptTerms(Request $request): JsonResponse
    {
        $user = auth()->user();
        $user->accepted_terms = true;
        $user->save();

        return response()->json(['success' => true]);
    }
}
