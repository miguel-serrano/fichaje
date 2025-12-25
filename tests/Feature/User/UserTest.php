<?php

namespace Tests\Feature\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create users_tests table if it doesn't exist (only in testing)
        if (!Schema::hasTable('users_tests')) {
            Schema::create('users_tests', function ($table) {
                $table->uuid('id')->primary();
                $table->string('email')->unique();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function test_can_create_user_via_web()
    {
        $email = 'test_' . time() . '@example.com'; // Unique email to avoid conflicts
        
        $response = $this->post('/users', [
            'name' => 'Test User',
            'email' => $email
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        // Verify user was created in users_tests table
        $this->assertDatabaseHas('users_tests', [
            'email' => $email,
            'name' => 'Test User',
            'is_active' => true
        ]);

        // Verify it's NOT in the regular users table
        $this->assertDatabaseMissing('users', [
            'email' => $email
        ]);
    }

    public function test_can_list_users()
    {
        // This will use users_tests table automatically
        $response = $this->get('/users');

        $response->assertStatus(200);
        $response->assertViewIs('users.index');
    }

    public function test_can_delete_user_via_web()
    {
        // First create a user
        $email = 'delete_test_' . time() . '@example.com';
        $createResponse = $this->post('/users', [
            'name' => 'User To Delete',
            'email' => $email
        ]);

        $createResponse->assertRedirect();
        
        // Get the created user ID
        $user = DB::table('users_tests')->where('email', $email)->first();
        $this->assertNotNull($user, 'User should exist before deletion');

        // Delete the user
        $deleteResponse = $this->delete(route('users.destroy', $user->id));

        $deleteResponse->assertRedirect(route('users.index'));
        $deleteResponse->assertSessionHas('success');

        // Verify user was deleted from users_tests table
        $this->assertDatabaseMissing('users_tests', [
            'id' => $user->id
        ]);
    }

    public function test_can_delete_user_via_api()
    {
        // First create a user
        $email = 'delete_api_' . time() . '@example.com';
        $createResponse = $this->postJson('/api/users', [
            'name' => 'User To Delete',
            'email' => $email
        ]);

        $createResponse->assertStatus(201);
        $userId = $createResponse->json('id');

        // Delete the user via API
        $deleteResponse = $this->deleteJson("/api/users/{$userId}");

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson([
            'message' => 'User deleted successfully'
        ]);

        // Verify user was deleted
        $this->assertDatabaseMissing('users_tests', [
            'id' => $userId
        ]);
    }

    public function test_delete_returns_404_when_user_not_found_via_api()
    {
        $nonExistentId = '123e4567-e89b-12d3-a456-426614174999';
        
        $response = $this->deleteJson("/api/users/{$nonExistentId}");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => "User {$nonExistentId} not found"
        ]);
    }

    public function test_delete_returns_error_when_user_not_found_via_web()
    {
        $nonExistentId = '123e4567-e89b-12d3-a456-426614174999';
        
        $response = $this->delete(route('users.destroy', $nonExistentId));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
    }

    public function test_debug_table_usage()
    {
        // This test helps verify which table is being used
        // Data will persist after test execution
        
        $email = 'debug_' . time() . '@example.com'; // Unique email to avoid conflicts
        
        // Show database and table info
        $currentDb = DB::connection()->getDatabaseName();
        $appEnv = app()->environment();
        $isTesting = app()->runningUnitTests();
        
        echo "\n=== DEBUG INFO ===\n";
        echo "Current Database: {$currentDb}\n";
        echo "APP_ENV: {$appEnv}\n";
        echo "Running Unit Tests: " . ($isTesting ? 'YES' : 'NO') . "\n";
        echo "Table being used: users_tests\n";
        echo "==================\n\n";
        
        $response = $this->post('/users', [
            'name' => 'Debug User',
            'email' => $email
        ]);

        // Count records in both tables
        $usersTestsCount = DB::table('users_tests')->count();
        $usersCount = DB::table('users')->count();

        echo "Users in users_tests: {$usersTestsCount}\n";
        echo "Users in users: {$usersCount}\n\n";

        // Verify it's using users_tests
        $this->assertGreaterThanOrEqual(1, $usersTestsCount, 'Should have at least 1 user in users_tests');
        $this->assertEquals(0, $usersCount, 'Should have 0 users in regular users table');
        
        // Verify the specific user was created
        $this->assertDatabaseHas('users_tests', [
            'email' => $email,
            'name' => 'Debug User'
        ]);
        
        // Show all users in users_tests
        $allUsers = DB::table('users_tests')->get();
        echo "All users in users_tests table:\n";
        foreach ($allUsers as $user) {
            echo "  - ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
        }
        echo "\n";
    }
}

