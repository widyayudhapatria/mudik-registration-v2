<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EmailSubmissionRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_respects_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/public/submit-email', [
                'email' => "test{$i}@example.com",
            ])->assertStatus(200);
        }

        $this->postJson('/public/submit-email', [
            'email' => 'test-blocked@example.com',
        ])->assertStatus(429);
    }

    public function test_it_allows_requests_after_rate_limit_window(): void
    {
        $firstEmail = 'first-batch@example.com';
        $secondEmail = 'second-batch@example.com';
        
        // Batch pertama - hit rate limit
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/public/submit-email', ['email' => $firstEmail]);
        }

        // Verify rate limit active
        $this->postJson('/public/submit-email', ['email' => $firstEmail])
            ->assertStatus(429);

        // Simulasi window baru dengan clear cache
        Cache::flush();

        // Batch kedua - harus bisa karena window baru
        $response = $this->postJson('/public/submit-email', ['email' => $secondEmail]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['email', 'expired_at']
            ]);
    }
}