<?php

namespace Tests\Feature\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BasicAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_user_can_view_login_form(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_user_can_view_register_form(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('bienvenido'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'is_active' => false,
        ]);
        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('registro_horario.index'));
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_guest_cannot_access_registro_horario(): void
    {
        $response = $this->get('/registro-horario');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_registro_horario(): void
    {
        $user = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/registro-horario');
        $response->assertStatus(200);
    }

    public function test_user_can_logout(): void
    {
        $user = \App\Models\User::create([
            'uuid' => \Illuminate\Support\Str::orderedUuid(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_cannot_register_more_than_8_users_per_day(): void
    {
        // Crear 8 usuarios hoy
        for ($i = 0; $i < 8; $i++) {
            \App\Models\User::create([
                'uuid' => \Illuminate\Support\Str::orderedUuid(),
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password123'),
                'is_active' => true,
                'created_at' => now(),
            ]);
        }

        // El 9º registro debe fallar
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_daily_registration_limit_resets_next_day(): void
    {
        // Crear 8 usuarios ayer usando DB para evitar timestamps automáticos
        $yesterday = now()->subDay();
        for ($i = 0; $i < 8; $i++) {
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'uuid' => \Illuminate\Support\Str::orderedUuid(),
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password123'),
                'is_active' => true,
                'created_at' => $yesterday,
                'updated_at' => $yesterday,
            ]);
        }

        // Hoy debe poder registrar
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('bienvenido'));
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }
}
