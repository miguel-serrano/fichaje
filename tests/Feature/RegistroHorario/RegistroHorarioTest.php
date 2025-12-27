<?php

namespace Tests\Feature\RegistroHorario;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistroHorarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear tablas necesarias para tests
        DB::statement('CREATE TABLE IF NOT EXISTS users_tests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME,
            updated_at DATETIME
        )');

        DB::statement('CREATE TABLE IF NOT EXISTS registro_horarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id TEXT NOT NULL,
            entrada DATETIME NOT NULL,
            salida DATETIME NULL,
            created_at DATETIME,
            updated_at DATETIME
        )');
    }

    public function test_can_view_registro_horario_index(): void
    {
        $response = $this->get('/registro-horario');
        
        $response->assertStatus(200);
        $response->assertViewIs('registro_horario');
        $response->assertViewHas(['users', 'segundos', 'selectedUserUuid', 'tieneRegistroAbierto']);
    }

    public function test_can_fichar_entrada(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->post('/registro-horario/entrada', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Entrada registrada correctamente');
        
        // Verificar que se creó el registro
        $this->assertDatabaseHas('registro_horarios', [
            'user_id' => $savedUser->uuid()->getValue(),
            'salida' => null
        ]);
    }

    public function test_can_fichar_salida(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        // Crear registro de entrada
        DB::table('registro_horarios')->insert([
            'user_id' => $savedUser->uuid()->getValue(),
            'entrada' => now()->subHours(8)->toDateTimeString(),
            'salida' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->post('/registro-horario/salida', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Salida registrada correctamente');
        
        // Verificar que se actualizó el registro con salida
        $this->assertDatabaseMissing('registro_horarios', [
            'user_id' => $savedUser->uuid()->getValue(),
            'salida' => null
        ]);
    }

    public function test_cannot_fichar_salida_without_entrada(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->post('/registro-horario/salida', [
            'userUuid' => $savedUser->uuid()->getValue()
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_registro_horario_validation(): void
    {
        // Test UUID requerido para entrada
        $response = $this->post('/registro-horario/entrada', []);
        $response->assertSessionHasErrors(['userUuid']);

        // Test UUID inválido para entrada
        $response = $this->post('/registro-horario/entrada', [
            'userUuid' => 'invalid-uuid'
        ]);
        $response->assertSessionHasErrors(['userUuid']);

        // Test UUID requerido para salida
        $response = $this->post('/registro-horario/salida', []);
        $response->assertSessionHasErrors(['userUuid']);

        // Test UUID inválido para salida
        $response = $this->post('/registro-horario/salida', [
            'userUuid' => 'invalid-uuid'
        ]);
        $response->assertSessionHasErrors(['userUuid']);
    }

    public function test_registro_horario_with_selected_user(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        // Crear algunos registros
        DB::table('registro_horarios')->insert([
            [
                'user_id' => $savedUser->uuid()->getValue(),
                'entrada' => now()->startOfDay()->addHours(9)->toDateTimeString(),
                'salida' => now()->startOfDay()->addHours(13)->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $savedUser->uuid()->getValue(),
                'entrada' => now()->startOfDay()->addHours(14)->toDateTimeString(),
                'salida' => now()->startOfDay()->addHours(18)->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $response = $this->get('/registro-horario?userUuid=' . $savedUser->uuid()->getValue());
        
        $response->assertStatus(200);
        $response->assertViewIs('registro_horario');
        
        // Verificar que se calcularon los segundos (8 horas = 28800 segundos)
        $viewData = $response->viewData('segundos');
        $this->assertEquals(28800, $viewData);
    }

    public function test_registro_horario_shows_open_registro(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        // Crear registro abierto (sin salida)
        DB::table('registro_horarios')->insert([
            'user_id' => $savedUser->uuid()->getValue(),
            'entrada' => now()->subHours(2)->toDateTimeString(),
            'salida' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->get('/registro-horario?userUuid=' . $savedUser->uuid()->getValue());
        
        $response->assertStatus(200);
        
        // Verificar que detecta registro abierto
        $tieneRegistroAbierto = $response->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);
    }
}
