<?php

namespace Tests\Feature\User;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private \App\Models\User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Crear y autenticar un usuario admin para todos los tests
        $this->authenticatedUser = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Admin Test User',
            'email' => 'admin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'is_active' => true,
            'remember_token' => 'soyAdm1n', // Admin user
        ]);

        $this->actingAs($this->authenticatedUser);
    }

    public function test_can_view_users_index_page(): void
    {
        $response = $this->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
        $response->assertViewHas('users');
    }

    public function test_can_view_users_create_page(): void
    {
        $response = $this->get('/users/create');

        $response->assertStatus(200);
        $response->assertViewIs('users.create');
    }

    public function test_can_create_user_via_web(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ];

        $response = $this->post('/users', $userData);

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User created successfully!');

        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => true,
        ]);
    }

    public function test_cannot_create_user_with_duplicate_email(): void
    {
        // Crear usuario inicial
        $this->post('/users', [
            'email' => 'test@example.com',
            'name' => 'First User',
        ]);

        // Intentar crear otro usuario con el mismo email
        $response = $this->post('/users', [
            'email' => 'test@example.com',
            'name' => 'Second User',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_can_view_specific_user(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->get("/users/{$savedUser->id()->getValue()}");

        $response->assertStatus(200);
        $response->assertViewIs('users.show');
        $response->assertViewHas('user');
    }

    public function test_can_delete_user(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->delete("/users/{$savedUser->id()->getValue()}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User deleted successfully!');

        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $savedUser->id()->getValue(),
        ]);
    }

    public function test_can_get_users_as_json(): void
    {
        // Crear algunos usuarios
        $repository = new EloquentUserRepository;
        $user1 = User::create(new Email('user1@example.com'), 'User One');
        $user2 = User::create(new Email('user2@example.com'), 'User Two');
        $repository->save($user1);
        $repository->save($user2);

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
    }

    public function test_user_validation_rules(): void
    {
        // Test email requerido
        $response = $this->post('/users', [
            'name' => 'Test User',
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test nombre requerido
        $response = $this->post('/users', [
            'email' => 'test@example.com',
        ]);
        $response->assertSessionHasErrors(['name']);

        // Test email inválido
        $response = $this->post('/users', [
            'email' => 'invalid-email',
            'name' => 'Test User',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_can_toggle_user_active_status(): void
    {
        // Crear un usuario
        $repository = new EloquentUserRepository;
        $user = User::create(new Email('toggle@example.com'), 'Toggle User');
        $savedUser = $repository->save($user);

        // Verificar que está activo inicialmente
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->getValue(),
            'is_active' => true,
        ]);

        // Desactivar el usuario
        $response = $this->patch("/users/{$savedUser->id()->getValue()}/toggle-active");
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario desactivado correctamente');

        // Verificar que se desactivó
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->getValue(),
            'is_active' => false,
        ]);

        // Activar el usuario nuevamente
        $response = $this->patch("/users/{$savedUser->id()->getValue()}/toggle-active");
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'Usuario activado correctamente');

        // Verificar que se activó
        $this->assertDatabaseHas('users', [
            'id' => $savedUser->id()->getValue(),
            'is_active' => true,
        ]);
    }
}
