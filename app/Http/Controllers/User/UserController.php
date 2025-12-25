<?php

namespace App\Http\Controllers\User;

use App\DDD\User\Application\CreateUserUseCase;
use App\DDD\User\Application\GetAllUsersUseCase;
use App\DDD\User\Application\GetUserByIdUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private CreateUserUseCase $createUserUseCase,
        private GetUserByIdUseCase $getUserUseCase,
        private GetAllUsersUseCase $getAllUsersUseCase
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255'
        ]);
        try {
            $user = $this->createUserUseCase->execute($validated['email'], $validated['name']);
            return response()->json($user->toArray(), 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function index(): JsonResponse
    {
        $users = $this->getAllUsersUseCase->execute();
        return response()->json($users);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = $this->getUserUseCase->execute($id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}