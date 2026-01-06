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
        $response->assertSessionHas('error', 'An open time entry already exists.');
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
        $response->assertSessionHas('error', 'No open time entry exists to close.');
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

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $openRegistro->id]));

        $response->assertRedirect(route('user.me'));
        $response->assertSessionHas('success', 'Fichaje cerrado correctamente');

        $this->assertDatabaseMissing('time_entries', [
            'id' => $openRegistro->id,
            'salida' => null,
        ]);
    }

    public function test_cannot_cerrar_registro_if_entry_not_found(): void
    {
        $registroId = 999;

        $response = $this->post(route('registro_horario.salida', ['registroHorarioId' => $registroId]));

        $response->assertRedirect(route('user.me'));
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
        // Hacer al usuario admin
        $this->authenticatedUser->is_admin = true;
        $this->authenticatedUser->save();

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

        $this->assertDatabaseCount('time_entries', 9);
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
}
