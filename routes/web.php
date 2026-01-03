<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\RegistroHorario\RegistroHorarioController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticationController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::get('/register', [AuthenticationController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthenticationController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');

    // Users
    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');
    Route::patch('/user/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('user.toggle-active');
    Route::delete('/user/{id}', [UserController::class, 'delete'])->name('user.delete');

    // Time tracking
    Route::get('/registro-horario', [RegistroHorarioController::class, 'index'])->name('registro_horario.index');
    Route::post('/registro-horario/entrada', [RegistroHorarioController::class, 'ficharEntrada'])->name('registro_horario.entrada');
    Route::post('/registro-horario/salida/{registroHorarioId?}', [RegistroHorarioController::class, 'ficharSalida'])->name('registro_horario.salida');
});
