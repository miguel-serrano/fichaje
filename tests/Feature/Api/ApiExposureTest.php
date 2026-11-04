<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

class ApiExposureTest extends TestCase
{
    private const REMEMBER_TOKEN = 'legacy-personal-code-123';

    public function testRememberTokenLookupEndpointNoLongerExists(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['remember_token' => self::REMEMBER_TOKEN])->save();

        $response = $this->getJson('/api/users/by-remember-token?code='.self::REMEMBER_TOKEN);

        $response->assertNotFound();
        $response->assertDontSee($user->email);
    }

    public function testSanctumUserEndpointNoLongerExists(): void
    {
        $this->getJson('/api/user')->assertNotFound();
    }

    public function testRememberTokenIsNotMassAssignable(): void
    {
        $user = new User(['name' => 'Test', 'remember_token' => self::REMEMBER_TOKEN]);

        $this->assertSame('Test', $user->name);
        $this->assertNull($user->remember_token);
    }

    public function testCorsRejectsForeignOrigins(): void
    {
        $response = $this->call('OPTIONS', '/api/anything', [], [], [], [
            'HTTP_ORIGIN' => 'https://evil.example.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);

        $allowedOrigin = $response->headers->get('Access-Control-Allow-Origin');

        $this->assertNotSame('*', $allowedOrigin);
        $this->assertNotSame('https://evil.example.com', $allowedOrigin);
    }

    public function testCorsAllowsTheApplicationOrigin(): void
    {
        $appUrl = config('app.url');

        $response = $this->call('OPTIONS', '/api/anything', [], [], [], [
            'HTTP_ORIGIN' => $appUrl,
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);

        $response->assertHeader('Access-Control-Allow-Origin', $appUrl);
    }
}
