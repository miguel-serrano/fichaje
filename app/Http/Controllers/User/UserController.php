<?php

namespace App\Http\Controllers\User;

use App\DDD\User\Application\CreateUserUseCase;
use App\DDD\User\Application\GetAllUsersUseCase;
use App\DDD\User\Application\GetUserByIdUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase,
        private GetUserByIdUseCase $getUserUseCase,
        private GetAllUsersUseCase $getAllUsersUseCase
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $users = $this->getAllUsersUseCase->execute();
        
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($users);
        }
        
        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255'
        ]);
        
        try {
            $user = $this->createUserUseCase->execute($validated['email'], $validated['name']);
            
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json($user->toArray(), 201);
            }
            
            return redirect()->route('users.index')
                ->with('success', 'User created successfully!');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            
            return back()
                ->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        }
    }

    public function show(Request $request, string $id): View|JsonResponse
    {
        try {
            $user = $this->getUserUseCase->execute($id);
            
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json($user);
            }
            
            return view('users.show', ['user' => $user]);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            
            return redirect()->route('users.index')
                ->with('error', $e->getMessage());
        }
    }
}