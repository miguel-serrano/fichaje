<?php

namespace Tests\Feature\User;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcceptTermsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        Mail::fake();
    }

    public function testUserAcceptsTerms(): void
    {
        $user = User::factory()->create(['accepted_terms' => false]);

        $response = $this->actingAs($user)->postJson(route('bienvenido.accept-terms'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'accepted_terms' => true]);
    }

    public function testAcceptingTermsTwiceIsIdempotent(): void
    {
        $user = User::factory()->create(['accepted_terms' => true]);

        $this->actingAs($user)->postJson(route('bienvenido.accept-terms'))->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'accepted_terms' => true]);
    }

    public function testAcceptingTermsOnlyAffectsTheAuthenticatedUser(): void
    {
        $user = User::factory()->create(['accepted_terms' => false]);
        $otherUser = User::factory()->create(['accepted_terms' => false]);

        $this->actingAs($user)->postJson(route('bienvenido.accept-terms'))->assertOk();

        $this->assertDatabaseHas('users', ['id' => $otherUser->id, 'accepted_terms' => false]);
    }

    public function testGuestCannotAcceptTerms(): void
    {
        $this->post(route('bienvenido.accept-terms'))->assertRedirect(route('login'));
    }

    public function testSavingTheUserForAnotherReasonKeepsAcceptedTerms(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole('super_admin');
        $user = User::factory()->create(['is_active' => true, 'accepted_terms' => true]);

        $this->actingAs($superAdmin)->patch(route('user.toggle-active', $user->id));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false, 'accepted_terms' => true]);
    }
}
