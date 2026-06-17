<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JwtValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that accessing loans without Bearer token returns 401.
     */
    public function test_access_without_token_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/v1/loans');

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Unauthorized: Bearer token missing',
                 ]);
    }

    /**
     * Test that accessing loans with an invalid JWT token returns 401.
     */
    public function test_access_with_invalid_token_returns_unauthorized(): void
    {
        $response = $this->withToken('invalid-token-string')
                         ->getJson('/api/v1/loans');

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                 ]);
        
        $this->assertStringContainsString('Unauthorized:', $response->json('message'));
    }
}
