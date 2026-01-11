<?php

namespace Tests\Unit;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_validates_required_fields(): void
    {
        $request = new StoreUserRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validates_email_format(): void
    {
        $request = new StoreUserRequest;

        $validator = Validator::make([
            'email' => 'invalid-email',
            'name' => 'Test User',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validates_unique_email(): void
    {
        // Create a user first
        User::factory()->create(['email' => 'existing@example.com']);

        $request = new StoreUserRequest;

        $validator = Validator::make([
            'email' => 'existing@example.com',
            'name' => 'Test User',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_passes_basic_validation_with_valid_data(): void
    {
        $request = new StoreUserRequest;

        $validator = Validator::make([
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_has_custom_messages(): void
    {
        $request = new StoreUserRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('name.string', $messages);
        $this->assertArrayHasKey('name.max', $messages);
    }

    public function test_authorize_returns_true(): void
    {
        $request = new StoreUserRequest;

        $this->assertTrue($request->authorize());
    }
}
