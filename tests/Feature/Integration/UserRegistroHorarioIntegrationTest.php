<?php

namespace Tests\Feature\Integration;

use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\Uuid;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserRegistroHorarioIntegrationTest extends TestCase
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
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Asignar rol super_admin para permisos de admin
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        if ($superAdminRole) {
            $this->authenticatedUser->roles()->attach($superAdminRole->id);
        }

        $this->actingAs($this->authenticatedUser);
    }

    public function test_users_index_shows_user_list(): void
    {
        // Crear usuario usando el modelo Eloquent
        $testUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');

        $users = $response->viewData('users');
        $this->assertNotEmpty($users);

        $foundUser = collect($users)->firstWhere('email', 'test@example.com');
        $this->assertNotNull($foundUser);
        $this->assertEquals('test@example.com', $foundUser['email']);
        $this->assertEquals('Test User', $foundUser['name']);
    }

    public function test_complete_workflow_registro_horario(): void
    {
        $entradaResponse = $this->post('/registro-horario/entrada');
        $entradaResponse->assertRedirect(route('user.me'));
        $entradaResponse->assertSessionHas('success');

        $registroResponse = $this->get('/registro-horario');
        $registroResponse->assertStatus(200);

        $tieneRegistroAbierto = $registroResponse->viewData('tieneRegistroAbierto');
        $this->assertTrue($tieneRegistroAbierto);

        sleep(1);

        $salidaResponse = $this->post('/registro-horario/salida');
        $salidaResponse->assertRedirect(route('user.me'));
        $salidaResponse->assertSessionHas('success');

        $finalResponse = $this->get('/registro-horario');
        $finalResponse->assertStatus(200);

        $tieneRegistroAbiertoFinal = $finalResponse->viewData('tieneRegistroAbierto');
        $this->assertFalse($tieneRegistroAbiertoFinal);
    }

    public function test_user_deletion_handles_registro_horario_gracefully(): void
    {
        // Crear usuario usando el modelo Eloquent
        $testUser = User::create([
            'uuid' => Str::orderedUuid(),
            'name' => 'Delete User',
            'email' => 'delete@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        // Usar el repositorio para fichar con el usuario del dominio
        $userEntity = $this->userRepository->findByUuid(new Uuid($testUser->uuid));
        $userEntity->clockIn();
        $this->userRepository->save($userEntity);
        $userEntity->clockOut();
        $this->userRepository->save($userEntity);

        $deleteResponse = $this->delete("/user/{$testUser->id}");
        $deleteResponse->assertRedirect('/users');
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $testUser->id,
        ]);

        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $testUser->id,
        ]);

        $usersResponse = $this->get('/users');
        $usersResponse->assertStatus(200);
    }
}
