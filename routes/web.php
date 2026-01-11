<?php

use App\Http\Controllers\Admin\Permission\DeletePermissionController;
use App\Http\Controllers\Admin\Permission\ListPermissionsController;
use App\Http\Controllers\Admin\Permission\StorePermissionController;
use App\Http\Controllers\Admin\Permission\UpdatePermissionController;
use App\Http\Controllers\Admin\Role\CreateRoleController;
use App\Http\Controllers\Admin\Role\DeleteRoleController;
use App\Http\Controllers\Admin\Role\EditRoleController;
use App\Http\Controllers\Admin\Role\ListRolesController;
use App\Http\Controllers\Admin\Role\ShowRoleController;
use App\Http\Controllers\Admin\Role\StoreRoleController;
use App\Http\Controllers\Admin\Role\SyncPermissionsController;
use App\Http\Controllers\Admin\Role\UpdateRoleController;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\BienvenidoController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RegistroHorario\RegistroHorarioController;
use App\Http\Controllers\User\AssignRoleToUserController;
use App\Http\Controllers\User\DeleteUserController;
use App\Http\Controllers\User\GetMyTimeEntriesController;
use App\Http\Controllers\User\ListUsersController;
use App\Http\Controllers\User\RemoveRoleFromUserController;
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

    Route::get('/bienvenida', [BienvenidoController::class, 'index'])->name('bienvenido');
    Route::post('/bienvenida/accept-terms', [BienvenidoController::class, 'acceptTerms'])->name('bienvenido.accept-terms');

    Route::get('/user/me', GetMyTimeEntriesController::class)->name('user.me');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::middleware('admin')->group(function () {
        Route::get('/users', ListUsersController::class)->name('users.index');
        Route::get('/user/{id}', ShowUserController::class)->name('user.show');
        Route::patch('/user/{id}/toggle-active', ToggleUserActiveController::class)->name('user.toggle-active');
        Route::delete('/user/{id}', DeleteUserController::class)->name('user.delete');
        Route::get('/admin/holidays', [HolidayAdminController::class, 'index'])->name('admin.holidays.index');
        Route::post('/admin/holidays/{id}/approve', [HolidayAdminController::class, 'approve'])->name('admin.holidays.approve');
        Route::post('/admin/holidays/{id}/reject', [HolidayAdminController::class, 'reject'])->name('admin.holidays.reject');
        Route::post('/user/{id}/roles', AssignRoleToUserController::class)->name('user.roles.assign');
        Route::delete('/user/{id}/roles/{roleSlug}', RemoveRoleFromUserController::class)->name('user.roles.remove');

        // Role Management
        Route::prefix('admin/roles')->name('admin.roles.')->group(function () {
            Route::get('/', ListRolesController::class)->name('index');
            Route::get('/create', CreateRoleController::class)->name('create');
            Route::post('/', StoreRoleController::class)->name('store');
            Route::get('/{id}', ShowRoleController::class)->name('show');
            Route::get('/{id}/edit', EditRoleController::class)->name('edit');
            Route::put('/{id}', UpdateRoleController::class)->name('update');
            Route::delete('/{id}', DeleteRoleController::class)->name('destroy');
            Route::put('/{id}/permissions', SyncPermissionsController::class)->name('permissions.sync');
        });

        // Permission Management
        Route::prefix('admin/permissions')->name('admin.permissions.')->group(function () {
            Route::get('/', ListPermissionsController::class)->name('index');
            Route::post('/', StorePermissionController::class)->name('store');
            Route::put('/{id}', UpdatePermissionController::class)->name('update');
            Route::delete('/{id}', DeletePermissionController::class)->name('destroy');
        });
    });

    Route::middleware('active')->group(function () {
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');

        Route::get('/registro-horario', [RegistroHorarioController::class, 'index'])->name('registro_horario.index');
        Route::post('/registro-horario/entrada', [RegistroHorarioController::class, 'ficharEntrada'])->name('registro_horario.entrada');
        Route::post('/registro-horario/salida/{registroHorarioId?}', [RegistroHorarioController::class, 'ficharSalida'])->name('registro_horario.salida');
    });
});
