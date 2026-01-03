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

    private \App\Models\User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrrfToken::class);
        $this->userRepository = $this->app->make(UserRepositoryInterface::class);
        $this->authenticatedUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'remember_token' => 'soyAdm1n',
        ]);

        $this->actingAs($this->authenticatedUser);
    }

    public function test_users_index_shows_accumulated_time(): void
    {
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $this->userRepository->save($user);

        $userWithTimeEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithTimeEntries->ficharEntrada();
        $this->userRepository->save($userWithTimeEntries);
        sleep(1);
        $userWithTimeEntries->ficharSalida();
        $this->userRepository->save($userWithTimeEntries);

        sleep(1);
        $userWithTimeEntries->ficharEntrada();
        $this->userRepository->save($userWithTimeEntries);
        sleep(1);
        $userWithTimeEntries->ficharSalida();
        $this->userRepository->save($userWithTimeEntries);

        $response = $this->get('/user');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');

        $users = $response->viewData('users');
        $this->assertNotEmpty($users);

        $testUser = collect($users)->firstWhere('email', 'test@example.com');
        $this->assertNotNull($testUser);
        $this->assertNotEquals('00:00:00', $testUser['tiempo_acumulado']);
    }

    public function test_users_json_api_includes_accumulated_time(): void
    {
        $user = User::create(new Email('api@example.com'), 'API User');
        $savedUser = $this->userRepository->save($user);

        $userWithTimeEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithTimeEntries->ficharEntrada();
        $this->userRepository->save($userWithTimeEntries);
        sleep(1);
        $userWithTimeEntries->ficharSalida();
        $this->userRepository->save($userWithTimeEntries);

        $response = $this->getJson('/user');

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

    public function test_complete_workflow_registro_horario(): void
    {
        $entradaResponse = $this->post('/registro-horario/entrada');
        $entradaResponse->assertRedirect(route('user.index'));
        $entradaResponse->assertSessionHas('success');

        $registroResponse = $this->get('/registro-horario');
        $registroResponse->assertStatus(200);

        $tieneRegistroAbierto = $registroResponse->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);

        sleep(1);

        $salidaResponse = $this->post('/registro-horario/salida');
        $salidaResponse->assertRedirect(route('user.index'));
        $salidaResponse->assertSessionHas('success');

        $finalResponse = $this->get('/registro-horario');
        $finalResponse->assertStatus(200);

        $tieneRegistroAbiertoFinal = $finalResponse->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbiertoFinal);

        $usersResponse = $this->get('/user');
        $users = $usersResponse->viewData('users');
        $adminUser = collect($users)->firstWhere('email', $this->authenticatedUser->email);

        $this->assertNotNull($adminUser);
        $this->assertNotEquals('00:00:00', $adminUser['tiempo_acumulado']);
    }

    public function test_user_deletion_handles_registro_horario_gracefully(): void
    {
        $user = User::create(new Email('delete@example.com'), 'Delete User');
        $savedUser = $this->userRepository->save($user);

        $userWithEntries = $this->userRepository->findById(new UserId($savedUser->id()->getValue()));
        $userWithEntries->ficharEntrada();
        $this->userRepository->save($userWithEntries);
        $userWithEntries->ficharSalida();
        $this->userRepository->save($userWithEntries);

        $deleteResponse = $this->delete("/user/{$savedUser->id()->getValue()}");
        $deleteResponse->assertRedirect('/user');
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->getValue(),
        ]);

        $this->assertDatabaseMissing('registro_horarios', [
            'user_id' => $savedUser->id()->getValue(),
        ]);

        $usersResponse = $this->get('/user');
        $usersResponse->assertStatus(200);
    }
}
