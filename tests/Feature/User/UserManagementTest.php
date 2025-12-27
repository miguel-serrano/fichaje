<?php

namespace Tests\Feature\User;

use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\ValueObjects\Email;
use App\DDD\User\Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear tabla de usuarios para tests
        DB::statement('CREATE TABLE IF NOT EXISTS users_tests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME,
            updated_at DATETIME
        )');
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
            'name' => 'Test User'
        ];

        $response = $this->post('/users', $userData);
        
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User created successfully!');
        
        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users_tests', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => true
        ]);
    }

    public function test_cannot_create_user_with_duplicate_email(): void
    {
        // Crear usuario inicial
        $this->post('/users', [
            'email' => 'test@example.com',
            'name' => 'First User'
        ]);

        // Intentar crear otro usuario con el mismo email
        $response = $this->post('/users', [
            'email' => 'test@example.com',
            'name' => 'Second User'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_can_view_specific_user(): void
    {
        // Crear un usuario primero
        $repository = new EloquentUserRepository();
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
        $repository = new EloquentUserRepository();
        $user = User::create(new Email('test@example.com'), 'Test User');
        $savedUser = $repository->save($user);

        $response = $this->delete("/users/{$savedUser->id()->getValue()}");
        
        $response->assertRedirect('/users');
        $response->assertSessionHas('success', 'User deleted successfully!');
        
        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users_tests', [
            'id' => $savedUser->id()->getValue()
        ]);
    }

    public function test_can_get_users_as_json(): void
    {
        // Crear algunos usuarios
        $repository = new EloquentUserRepository();
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
                'tiempo_acumulado'
            ]
        ]);
    }

    public function test_user_validation_rules(): void
    {
        // Test email requerido
        $response = $this->post('/users', [
            'name' => 'Test User'
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test nombre requerido
        $response = $this->post('/users', [
            'email' => 'test@example.com'
        ]);
        $response->assertSessionHasErrors(['name']);

        // Test email inválido
        $response = $this->post('/users', [
            'email' => 'invalid-email',
            'name' => 'Test User'
        ]);
        $response->assertSessionHasErrors(['email']);
    }
}
