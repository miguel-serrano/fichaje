<?php

namespace Tests\Feature\Integration;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Domain\ValueObjects\UserId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistroHorarioIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        // Resolve the UserRepositoryInterface from the service container
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
    }

    public function test_users_index_shows_accumulated_time(): void
    {
        // Crear un usuario
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user);

        // Cargar el usuario, fichar entrada y salida varias veces para el mismo día
        $userWithTimeEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithTimeEntries->ficharEntrada(); // 1st entry (open)
        $this->userRepository->save($userWithTimeEntries);
        sleep(1);
        $userWithTimeEntries->ficharSalida(); // Close 1st entry
        $this->userRepository->save($userWithTimeEntries);

        sleep(1);
        $userWithTimeEntries->ficharEntrada(); // 2nd entry (open)
        $this->userRepository->save($userWithTimeEntries);
        sleep(1);
        $userWithTimeEntries->ficharSalida(); // Close 2nd entry
        $this->userRepository->save($userWithTimeEntries); // Save final state

        $response = $this->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');

        // Verificar que la vista contiene usuarios con tiempo acumulado
        $users = $response->viewData('users');
        $this->assertNotEmpty($users);

        $testUser = collect($users)->firstWhere('email', 'test@example.com');
        $this->assertNotNull($testUser);
        $this->assertNotEquals('00:00:00', $testUser['tiempo_acumulado']); // Should be > 0
    }

    public function test_users_json_api_includes_accumulated_time(): void
    {
        // Crear un usuario
        $user = User::create(new Email('api@example.com'), 'API User');
        $savedUser = $this->userRepository->save($user);

        // Cargar el usuario, fichar entrada y salida
        $userWithTimeEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithTimeEntries->ficharEntrada(); // Open
        $this->userRepository->save($userWithTimeEntries);
        sleep(1);
        $userWithTimeEntries->ficharSalida(); // Close
        $this->userRepository->save($userWithTimeEntries);

        $response = $this->getJson('/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'uuid',
                'email',
                'name',
                'is_active',
                'tiempo_acumulado',
            ],
        ]);

        $users = $response->json();
        $apiUser = collect($users)->firstWhere('email', 'api@example.com');

        $this->assertNotNull($apiUser);
        $this->assertNotEquals('00:00:00', $apiUser['tiempo_acumulado']);
    }

    public function test_complete_workflow_create_user_and_registro(): void
    {
        // 1. Crear usuario via web
        $userData = [
            'email' => 'workflow@example.com',
            'name' => 'Workflow User',
        ];

        $createResponse = $this->post('/users', $userData);
        $createResponse->assertRedirect('/users');

        // 2. Obtener el usuario creado desde el repositorio para obtener su ID
        $allUsers = $this->userRepository->findAll();
        $createdUser = collect($allUsers)->first(function ($user) {
            return $user->email()->getValue() === 'workflow@example.com';
        });

        $this->assertNotNull($createdUser);

        // 3. Fichar entrada
        $entradaResponse = $this->post('/registro-horario/entrada', [
            'userUuid' => $createdUser->uuid()->getValue(),
        ]);
        $entradaResponse->assertRedirect();
        $entradaResponse->assertSessionHas('success');

        // 4. Verificar que aparece en registro horario
        $registroResponse = $this->get('/registro-horario?userUuid='.$createdUser->uuid()->getValue());
        $registroResponse->assertStatus(200);

        $tieneRegistroAbierto = $registroResponse->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);

        // 5. Esperar un segundo para simular tiempo trabajado
        sleep(1);

        // 5. Fichar salida
        $salidaResponse = $this->post('/registro-horario/salida', [
            'userUuid' => $createdUser->uuid()->getValue(),
        ]);
        $salidaResponse->assertRedirect();
        $salidaResponse->assertSessionHas('success');

        // 6. Verificar que el registro se cerró
        $finalResponse = $this->get('/registro-horario?userUuid='.$createdUser->uuid()->getValue());
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
        // Crear usuario
        $user = User::create(new Email('delete@example.com'), 'Delete User');
        $savedUser = $this->userRepository->save($user);

        // Cargar el usuario y crear registros horarios
        $userWithEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithEntries->ficharEntrada();
        $this->userRepository->save($userWithEntries); // Save open entry
        $userWithEntries->ficharSalida();
        $this->userRepository->save($userWithEntries); // Save closed entry

        // Eliminar usuario
        $deleteResponse = $this->delete("/users/{$savedUser->id()->getValue()}");
        $deleteResponse->assertRedirect('/users');
        $deleteResponse->assertSessionHas('success');

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->getValue(),
        ]);

        // Verificar que los registros horarios asociados también fueron eliminados
        $this->assertDatabaseMissing('registro_horarios', [
            'user_id' => $savedUser->id()->getValue(),
        ]);

        $usersResponse = $this->get('/users');
        $usersResponse->assertStatus(200);
    }
}
