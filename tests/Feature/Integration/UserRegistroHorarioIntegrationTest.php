<?php

namespace Tests\Feature\Integration;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRegistroHorarioIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // RefreshDatabase se encarga de las migraciones automáticamente
    }

    public function test_users_index_shows_accumulated_time(): void
    {
        // Crear un usuario
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        // Crear registros horarios para el usuario
        DB::table('registro_horarios')->insert([
            [
                'user_id' => $savedUser->uuid()->getValue(),
                'entrada' => now()->startOfDay()->addHours(9)->toDateTimeString(),
                'salida' => now()->startOfDay()->addHours(13)->toDateTimeString(), // 4 horas
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => $savedUser->uuid()->getValue(),
                'entrada' => now()->startOfDay()->addHours(14)->toDateTimeString(),
                'salida' => now()->startOfDay()->addHours(18)->toDateTimeString(), // 4 horas
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        $response = $this->get('/users');
        
        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        
        // Verificar que la vista contiene usuarios con tiempo acumulado
        $users = $response->viewData('users');
        $this->assertNotEmpty($users);
        
        $testUser = collect($users)->firstWhere('email', 'test@example.com');
        $this->assertNotNull($testUser);
        $this->assertEquals('08:00:00', $testUser['tiempo_acumulado']); // 8 horas total
    }

    public function test_users_json_api_includes_accumulated_time(): void
    {
        // Crear un usuario
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('api@example.com'), 'API User');
        $savedUser = $repository->save($user);

        // Crear registro horario
        DB::table('registro_horarios')->insert([
            'user_id' => $savedUser->uuid()->getValue(),
            'entrada' => now()->startOfDay()->addHours(9)->toDateTimeString(),
            'salida' => now()->startOfDay()->addHours(17)->toDateTimeString(), // 8 horas
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson('/users');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'uuid',
                'email',
                'name',
                'is_active',
                'tiempo_acumulado'
            ]
        ]);

        $users = $response->json();
        $apiUser = collect($users)->firstWhere('email', 'api@example.com');
        
        $this->assertNotNull($apiUser);
        $this->assertEquals('08:00:00', $apiUser['tiempo_acumulado']);
    }

    public function test_complete_workflow_create_user_and_registro(): void
    {
        // 1. Crear usuario via web
        $userData = [
            'email' => 'workflow@example.com',
            'name' => 'Workflow User'
        ];

        $createResponse = $this->post('/users', $userData);
        $createResponse->assertRedirect('/users');

        // 2. Obtener el usuario creado
        $repository = new EloquentUserRepository();
        $users = $repository->findAll();
        $createdUser = collect($users)->first(function ($user) {
            return $user->email()->getValue() === 'workflow@example.com';
        });

        $this->assertNotNull($createdUser);

        // 3. Fichar entrada
        $entradaResponse = $this->post('/registro-horario/entrada', [
            'userUuid' => $createdUser->uuid()->getValue()
        ]);
        $entradaResponse->assertRedirect();
        $entradaResponse->assertSessionHas('success');

        // 4. Verificar que aparece en registro horario
        $registroResponse = $this->get('/registro-horario?userUuid=' . $createdUser->uuid()->getValue());
        $registroResponse->assertStatus(200);
        
        $tieneRegistroAbierto = $registroResponse->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);

        // 5. Esperar un segundo para simular tiempo trabajado
        sleep(1);
        
        // 5. Fichar salida
        $salidaResponse = $this->post('/registro-horario/salida', [
            'userUuid' => $createdUser->uuid()->getValue()
        ]);
        $salidaResponse->assertRedirect();
        $salidaResponse->assertSessionHas('success');

        // 6. Verificar que el registro se cerró
        $finalResponse = $this->get('/registro-horario?userUuid=' . $createdUser->uuid()->getValue());
        $finalResponse->assertStatus(200);
        
        $tieneRegistroAbiertoFinal = $finalResponse->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbiertoFinal);

        // 7. Verificar que aparece tiempo en users index
        $usersResponse = $this->get('/users');
        $users = $usersResponse->viewData('users');
        $workflowUser = collect($users)->firstWhere('email', 'workflow@example.com');
        
        $this->assertNotNull($workflowUser);
        $this->assertNotEquals('00:00:00', $workflowUser['tiempo_acumulado']);
    }

    public function test_user_deletion_handles_registro_horario_gracefully(): void
    {
        // Crear usuario con registros
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('delete@example.com'), 'Delete User');
        $savedUser = $repository->save($user);

        // Crear registros horarios
        DB::table('registro_horarios')->insert([
            'user_id' => $savedUser->uuid()->getValue(),
            'entrada' => now()->subHours(8)->toDateTimeString(),
            'salida' => now()->subHours(4)->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Eliminar usuario
        $deleteResponse = $this->delete("/users/{$savedUser->id()->getValue()}");
        $deleteResponse->assertRedirect('/users');
        $deleteResponse->assertSessionHas('success');

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->getValue()
        ]);

        // Los registros horarios pueden permanecer (depende de la lógica de negocio)
        // pero no deberían causar errores en la aplicación
        $usersResponse = $this->get('/users');
        $usersResponse->assertStatus(200);
    }
}
