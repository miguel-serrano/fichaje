<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroHorario\RegistroHorarioController;

// ... otras rutas ...

use App\Http\Controllers\User\UserController;

// Rutas de usuarios
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');


Route::get('/registro-horario', [RegistroHorarioController::class, 'index'])->name('registro_horario.index');
Route::post('/registro-horario/entrada', [RegistroHorarioController::class, 'ficharEntrada'])->name('registro_horario.entrada');
Route::post('/registro-horario/salida', [RegistroHorarioController::class, 'ficharSalida'])->name('registro_horario.salida');
