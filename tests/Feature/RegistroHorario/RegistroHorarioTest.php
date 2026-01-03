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
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
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

        $response->assertRedirect(route('user.index'));
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

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error', 'Ya existe un registro de entrada abierto.');
    }

    public function test_can_fichar_salida(): void
    {
        $this->post('/registro-horario/entrada');

        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success', 'Salida registrada correctamente');

        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $this->authenticatedUser->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_fichar_salida_without_entrada(): void
    {
        $response = $this->post('/registro-horario/salida');

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error', 'No existe un registro de entrada abierto para cerrar.');
    }

    // Test removido: la validación de userUuid ya no es necesaria porque se obtiene del usuario autenticado

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

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $openRegistro->id]));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('success', 'Fichaje cerrado correctamente');

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

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error');
    }

    public function test_cannot_cerrar_registro_if_entry_already_closed(): void
    {
        $this->post('/registro-horario/entrada');
        $this->post('/registro-horario/salida');

        $closedRegistro = \App\Models\TimeEntry::query()
            ->where('user_id', $this->authenticatedUser->id)
            ->whereNotNull('salida')
            ->first();

        $this->assertNotNull($closedRegistro);

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $closedRegistro->id]));

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error');
    }

    public function test_inactive_user_cannot_fichar_entrada(): void
    {
        $this->authenticatedUser->is_active = false;
        $this->authenticatedUser->save();

        $response = $this->post('/registro-horario/entrada');

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');

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

        $response->assertRedirect(route('user.index'));
        $response->assertSessionHas('error', 'Tu cuenta está inactiva. Contacta con un administrador para activarla.');
    }
}
