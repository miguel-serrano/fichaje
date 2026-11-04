<?php

namespace Tests\Feature\Authentication;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthenticationThrottleTest extends TestCase
{
    private const PASSWORD = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function testLoginIsBlockedAfterTooManyFailedAttempts(): void
    {
        $user = $this->createUser('throttled@example.com');

        $this->failLoginAttempts($user->email, RouteServiceProvider::LOGIN_ATTEMPTS_PER_MINUTE);

        $response = $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertRedirect();
        $this->assertTooManyAttempts($response);
        $response->assertSessionHasInput('email', $user->email);
        $this->assertGuest();
    }

    public function testLoginIsAllowedBeforeReachingTheLimit(): void
    {
        $user = $this->createUser('almost@example.com');

        $this->failLoginAttempts($user->email, RouteServiceProvider::LOGIN_ATTEMPTS_PER_MINUTE - 1);

        $response = $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertRedirect(route('registro_horario.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function testLoginFromAnotherIpIsNotBlocked(): void
    {
        $user = $this->createUser('other-ip@example.com');

        $this->failLoginAttempts($user->email, RouteServiceProvider::LOGIN_ATTEMPTS_PER_MINUTE);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertRedirect(route('registro_horario.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function testLoginThrottleIsCaseInsensitiveOnEmail(): void
    {
        $user = $this->createUser('case@example.com');

        $this->failLoginAttempts('CASE@example.com', RouteServiceProvider::LOGIN_ATTEMPTS_PER_MINUTE);

        $response = $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        $this->assertTooManyAttempts($response);
        $this->assertGuest();
    }

    public function testLoginIsBlockedPerIpWhenTryingManyEmails(): void
    {
        $user = $this->createUser('target@example.com');

        for ($attempt = 0; $attempt < RouteServiceProvider::LOGIN_ATTEMPTS_PER_IP_PER_MINUTE; ++$attempt) {
            $this->post('/login', ['email' => "random{$attempt}@example.com", 'password' => 'wrong-password']);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD]);

        $this->assertTooManyAttempts($response);
        $this->assertGuest();
    }

    public function testRegisterIsBlockedAfterTooManyAttempts(): void
    {
        for ($attempt = 0; $attempt < RouteServiceProvider::REGISTER_ATTEMPTS_PER_MINUTE; ++$attempt) {
            $this->post('/register', ['name' => '', 'email' => 'invalid', 'password' => 'x']);
        }

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new-throttled@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        $response->assertRedirect();
        $this->assertTooManyAttempts($response);
        $this->assertDatabaseMissing('users', ['email' => 'new-throttled@example.com']);
    }

    private function createUser(string $email): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'is_active' => true,
        ]);
    }

    private function failLoginAttempts(string $email, int $attempts): void
    {
        for ($attempt = 0; $attempt < $attempts; ++$attempt) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        }
    }

    private function assertTooManyAttempts(TestResponse $response): void
    {
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
    }
}
