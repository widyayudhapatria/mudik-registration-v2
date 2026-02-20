<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\FormLinkStatus;
use App\Models\FormLink;
use App\Jobs\SendFormLinkEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class EmailSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable rate limiting for all tests in this class
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    /** @test */
    public function it_submits_email_successfully(): void
    {
        Queue::fake();

        $response = $this->postJson('/public/submit-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['email', 'expired_at'],
            ]);

        $this->assertDatabaseHas('form_links', [
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Pending->value,
        ]);

        Queue::assertPushed(SendFormLinkEmail::class);
    }

    /** @test */
    public function it_rejects_invalid_email_format(): void
    {
        $response = $this->postJson('/public/submit-email', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_rejects_duplicate_submitted_email(): void
    {
        FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Submitted->value,
        ]);

        $response = $this->postJson('/public/submit-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'EMAIL_EXISTS',
            ]);
    }

    /** @test */
    public function it_rejects_duplicate_approved_email(): void
    {
        FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Approved->value,
        ]);

        $response = $this->postJson('/public/submit-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error_code' => 'EMAIL_EXISTS',
            ]);
    }

    /** @test */
    public function it_allows_resubmission_for_rejected_email(): void
    {
        Queue::fake();
        
        $oldFormLink = FormLink::factory()->create([
            'email' => 'test@example.com',
            'status' => FormLinkStatus::Rejected->value,
            'token' => 'old-token',
            'resend_count' => 1,
        ]);

        $response = $this->postJson('/public/submit-email', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);

        $formLink = FormLink::where('email', 'test@example.com')->first();
        
        $this->assertEquals($oldFormLink->id, $formLink->id);
        $this->assertNotEquals('old-token', $formLink->token);
        $this->assertEquals(FormLinkStatus::Pending->value, $formLink->status);
        $this->assertEquals(2, $formLink->resend_count);
    }

    /** @test */
    public function it_requires_email_field(): void
    {
        $response = $this->postJson('/public/submit-email', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}