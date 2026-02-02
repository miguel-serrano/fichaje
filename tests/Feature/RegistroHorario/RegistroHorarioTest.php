<?php

namespace Tests\Feature\RegistroHorario;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistroHorarioTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Ejecutar seeders de roles y permisos
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
        $this->authenticatedUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Asignar rol employee para tener permisos de fichar
        $employeeRole = Role::where('slug', 'employee')->first();
        if ($employeeRole) {
            $this->authenticatedUser->roles()->attach($employeeRole->id);
        }

        $this->actingAs($this->authenticatedUser);
    }

    public function test_can_view_registro_horario_index(): void
    {
        $response = $this->get('/registro-horario');

        $response->assertStatus(200);
        $response->assertViewIs('registro_horario');
        $response->assertViewHas(['user', 'segundos', 'tieneRegistroAbierto']);
    }

    public function test_can_fichar_entrada(): void
    {
        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('success', 'Entrada registrada correctamente');

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->authenticatedUser->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_fichar_entrada_if_already_open(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('error', 'Ya existe una entrada de tiempo abierta');
    }

    public function test_can_fichar_salida(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('success', 'Salida registrada correctamente');

        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $this->authenticatedUser->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_fichar_salida_without_entrada(): void
    {
        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('error', 'No hay una entrada de tiempo abierta para cerrar');
    }

    public function test_registro_horario_with_multiple_entries(): void
    {
        $this->post('/registro-horario/entrada');
        sleep(1);
        $this->post('/registro-horario/salida');

        sleep(1);
        $this->post('/registro-horario/entrada');
        sleep(1);
        $this->post('/registro-horario/salida');

        $response = $this->get('/registro-horario');

        $response->assertStatus(200);
        $response->assertViewIs('registro_horario');

        $viewData = $response->viewData('segundos');
        $this->assertGreaterThan(0, $viewData);
    }

    public function test_registro_horario_shows_open_registro(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->get('/registro-horario');

        $response->assertStatus(200);

        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);
    }

    public function test_registro_horario_does_not_show_open_registro_if_all_closed(): void
    {
        $this->post('/registro-horario/entrada');
        $this->post('/registro-horario/salida');

        $response = $this->get('/registro-horario');

        $response->assertStatus(200);

        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbierto);
    }

    public function test_can_cerrar_registro_successfully(): void
    {
        $this->post('/registro-horario/entrada');

        $openRegistro = \App\Models\TimeEntry::query()
            ->where('user_id', $this->authenticatedUser->id)
            ->whereNull('salida')
            ->first();

        $this->assertNotNull($openRegistro);

        $response = $this->post(route('registro_horario.salida'));

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('success', 'Salida registrada correctamente');

        $this->assertDatabaseMissing('time_entries', [
            'id' => $openRegistro->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_cerrar_registro_if_no_open_entry(): void
    {
        $response = $this->post(route('registro_horario.salida'));

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('error');
    }

    public function test_inactive_user_cannot_fichar_entrada(): void
    {
        $this->authenticatedUser->is_active = false;
        $this->authenticatedUser->save();

        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('bienvenido'));
        $response->assertSessionHas('error', 'Tu cuenta está pendiente de activación, en breve se activará.');

        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $this->authenticatedUser->id,
        ]);
    }

    public function test_inactive_user_cannot_fichar_salida(): void
    {
        $this->post('/registro-horario/entrada');

        $this->authenticatedUser->is_active = false;
        $this->authenticatedUser->save();

        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('bienvenido'));
        $response->assertSessionHas('error', 'Tu cuenta está pendiente de activación, en breve se activará.');
    }

    public function test_user_cannot_exceed_daily_time_entry_limit(): void
    {
        // Crear 8 registros (el máximo permitido)
        for ($i = 0; $i < 8; $i++) {
            \App\Models\TimeEntry::create([
                'user_id' => $this->authenticatedUser->id,
                'entrada' => now(),
                'salida' => now()->addMinutes(30),
            ]);
        }

        // El 9º intento debe fallar
        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('error');

        $session = session('error');
        $this->assertStringContainsString('límite máximo de 8 fichajes', $session);
    }

    public function test_admin_can_exceed_daily_time_entry_limit(): void
    {
        // Hacer al usuario super_admin (tiene todos los permisos)
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $this->authenticatedUser->roles()->sync([$superAdminRole->id]);

        // Crear 8 registros
        for ($i = 0; $i < 8; $i++) {
            \App\Models\TimeEntry::create([
                'user_id' => $this->authenticatedUser->id,
                'entrada' => now(),
                'salida' => now()->addMinutes(30),
            ]);
        }

        // El 9º intento debe funcionar para admin
        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('success', 'Entrada registrada correctamente');

        // Verificar que el admin tiene 9 registros (8 creados + 1 nuevo)
        $this->assertEquals(9, \App\Models\TimeEntry::where('user_id', $this->authenticatedUser->id)->count());
    }

    public function test_daily_limit_resets_for_new_day(): void
    {
        // Crear 8 registros de ayer
        for ($i = 0; $i < 8; $i++) {
            \App\Models\TimeEntry::create([
                'user_id' => $this->authenticatedUser->id,
                'entrada' => now()->subDay(),
                'salida' => now()->subDay()->addMinutes(30),
            ]);
        }

        // Hoy debe poder fichar
        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('success', 'Entrada registrada correctamente');
    }

    // ===== Tests de UI - Validación de botones =====

    public function test_registro_horario_shows_fichar_entrada_button(): void
    {
        $response = $this->get('/registro-horario');

        $response->assertStatus(200);
        $response->assertSee('Fichar Entrada');
    }

    public function test_registro_horario_shows_warning_message_when_registro_abierto(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->get('/registro-horario');

        $response->assertStatus(200);
        $response->assertSee('Tienes un fichaje abierto');
        $response->assertSee('tu página de fichajes');
    }

    public function test_registro_horario_shows_error_for_user_without_timetracking_permission(): void
    {
        // Quitar todos los roles al usuario
        $this->authenticatedUser->roles()->detach();

        $response = $this->get('/registro-horario');

        // Usuario sin permisos ve un mensaje de error (AccessDeniedException)
        $response->assertSee('Permisos insuficientes para ejecutar el atributo');
    }

    public function test_user_me_shows_cerrar_button_when_registro_abierto(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Cerrar fichaje');
    }

    public function test_user_me_shows_informacion_personal_section(): void
    {
        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Información Personal');
    }

    public function test_user_me_shows_fichaje_de_hoy_section(): void
    {
        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Fichaje de hoy');
    }

    public function test_user_me_shows_todos_los_fichajes_section(): void
    {
        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Todos los Fichajes');
    }

    public function test_user_me_shows_resumen_diario_section(): void
    {
        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Resumen Diario');
    }

    public function test_user_me_shows_abierto_status_for_open_registro(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Abierto');
    }

    public function test_user_me_shows_cerrado_status_for_closed_registro(): void
    {
        $this->post('/registro-horario/entrada');
        $this->post('/registro-horario/salida');

        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertSee('Cerrado');
    }

    public function test_user_me_does_not_show_cerrar_button_when_no_open_registro(): void
    {
        // Crear un registro cerrado
        $this->post('/registro-horario/entrada');
        $this->post('/registro-horario/salida');

        $response = $this->get(route('user.me'));

        $response->assertStatus(200);
        $response->assertDontSee('Cerrar fichaje');
    }
}
