<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('users.index'));
    }

    /**
     * Test that users index page returns a successful response.
     *
     * @return void
     */
    public function test_users_index_returns_a_successful_response()
    {
        $response = $this->get('/users');

        $response->assertStatus(200);
    }
}
