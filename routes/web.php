<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\RegistroHorario\RegistroHorarioController;
use App\Http\Controllers\User\DeleteUserController;
use App\Http\Controllers\User\GetMyTimeEntriesController;
use App\Http\Controllers\User\ListUsersController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\ToggleUserActiveController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticationController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::get('/register', [AuthenticationController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthenticationController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');

    Route::get('/user/me', GetMyTimeEntriesController::class)->name('user.me');

    Route::middleware('admin')->group(function () {
        Route::get('/users', ListUsersController::class)->name('users.index');
        Route::get('/user/{id}', ShowUserController::class)->name('user.show');
        Route::patch('/user/{id}/toggle-active', ToggleUserActiveController::class)->name('user.toggle-active');
        Route::delete('/user/{id}', DeleteUserController::class)->name('user.delete');
    });

    Route::get('/registro-horario', [RegistroHorarioController::class, 'index'])->name('registro_horario.index');
    Route::post('/registro-horario/entrada', [RegistroHorarioController::class, 'ficharEntrada'])->name('registro_horario.entrada');
    Route::post('/registro-horario/salida/{registroHorarioId?}', [RegistroHorarioController::class, 'ficharSalida'])->name('registro_horario.salida');
});
