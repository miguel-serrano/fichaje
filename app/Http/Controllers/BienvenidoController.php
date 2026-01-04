<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BienvenidoController extends Controller
{
    public function index(): View
    {
        return view('bienvenido');
    }

    public function acceptTerms(Request $request): JsonResponse
    {
        $user = auth()->user();
        $user->accepted_terms = true;
        $user->save();

        return response()->json(['success' => true]);
    }
}
