<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_returns_a_successful_response(): void
    {
        // Create a user and ensure they are verified
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Dump the authenticated user for debugging
        // dump(auth()->user());

        // Make a GET request to the dashboard route
        $response = $this->get('/dashboard');

        // Dump the response for debugging
        // dump($response->status());
        // dump($response->headers->get('Location')); // Check for redirects

        // Assert that the response status is 200 OK
        $response->assertStatus(200);
    }
}