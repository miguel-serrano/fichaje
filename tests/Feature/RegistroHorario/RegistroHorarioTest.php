<?php

namespace Tests\Feature\RegistroHorario;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroHorarioTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $userRepository;

    private \App\Models\User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // Resolve the UserRepositoryInterface from the service container
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);

        // Crear y autenticar un usuario para todos los tests
        $this->authenticatedUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'testuser@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
        ]);

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

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'Entrada registrada correctamente');

        // Verificar que se creó el registro para el usuario autenticado
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->authenticatedUser->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_fichar_entrada_if_already_open(): void
    {
        // Fichar entrada primero
        $this->post('/registro-horario/entrada');

        // Intentar fichar entrada de nuevo
        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'Ya existe un registro de entrada abierto.');
    }

    public function test_can_fichar_salida(): void
    {
        // Fichar entrada primero
        $this->post('/registro-horario/entrada');

        // Fichar salida
        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'Salida registrada correctamente');

        // Verificar que se actualizó el registro con salida
        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $this->authenticatedUser->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_fichar_salida_without_entrada(): void
    {
        // Intentar fichar salida sin haber fichado entrada
        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'No existe un registro de entrada abierto para cerrar.');
    }

    // Test removido: la validación de userUuid ya no es necesaria porque se obtiene del usuario autenticado

    public function test_registro_horario_with_multiple_entries(): void
    {
        // Fichar entrada y salida varias veces para el mismo día
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

        // Verificar que se calcularon los segundos
        $viewData = $response->viewData('segundos');
        $this->assertGreaterThan(0, $viewData); // Should be > 0
    }

    public function test_registro_horario_shows_open_registro(): void
    {
        // Fichar entrada
        $this->post('/registro-horario/entrada');

        $response = $this->get('/registro-horario');

        $response->assertStatus(200);

        // Verificar que detecta registro abierto
        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);
    }

    public function test_registro_horario_does_not_show_open_registro_if_all_closed(): void
    {
        // Fichar entrada y salida
        $this->post('/registro-horario/entrada');
        $this->post('/registro-horario/salida');

        $response = $this->get('/registro-horario');

        $response->assertStatus(200);

        // Verificar que no detecta registro abierto
        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbierto);
    }

    // New tests for cerrarRegistro
    public function test_can_cerrar_registro_successfully(): void
    {
        // Fichar entrada
        $this->post('/registro-horario/entrada');

        // Obtener el registro abierto
        $openRegistro = \App\Models\TimeEntry::query()
            ->where('user_id', $this->authenticatedUser->id)
            ->whereNull('salida')
            ->first();

        $this->assertNotNull($openRegistro);

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $openRegistro->id]));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'Fichaje cerrado correctamente');

        // Verificar que el registro está cerrado en la BD
        $this->assertDatabaseMissing('time_entries', [
            'id' => $openRegistro->id,
            'salida' => null,
        ]);
    }

    // Test removido: ya no se puede probar "usuario no encontrado" porque el usuario viene autenticado

    public function test_cannot_cerrar_registro_if_entry_not_found(): void
    {
        $registroId = 999; // Non-existent RegistroHorario ID

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $registroId]));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
    }

    public function test_cannot_cerrar_registro_if_entry_already_closed(): void
    {
        // Fichar entrada y salida
        $this->post('/registro-horario/entrada');
        $this->post('/registro-horario/salida');

        // Obtener el registro cerrado
        $closedRegistro = \App\Models\TimeEntry::query()
            ->where('user_id', $this->authenticatedUser->id)
            ->whereNotNull('salida')
            ->first();

        $this->assertNotNull($closedRegistro);

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $closedRegistro->id]));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
    }

    public function test_inactive_user_cannot_fichar_entrada(): void
    {
        // Desactivar al usuario autenticado
        $this->authenticatedUser->is_active = false;
        $this->authenticatedUser->save();

        // Intentar fichar entrada
        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');

        // Verificar que NO se creó el registro
        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $this->authenticatedUser->id,
        ]);
    }

    public function test_inactive_user_cannot_fichar_salida(): void
    {
        // Fichar entrada mientras está activo
        $this->post('/registro-horario/entrada');

        // Desactivar al usuario
        $this->authenticatedUser->is_active = false;
        $this->authenticatedUser->save();

        // Intentar fichar salida
        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');
    }
}
