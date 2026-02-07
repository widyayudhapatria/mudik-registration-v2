<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendFormLinkEmail;
use App\Models\EmailLog;
use App\Models\FormLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendFormLinkEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_email_successfully(): void
    {
        Mail::fake();

        $formLink = FormLink::factory()->create();

        $job = new SendFormLinkEmail($formLink);
        $job->handle();

        // Assert email was sent
        Mail::assertSent(function ($mail) use ($formLink) {
            return $mail->hasTo($formLink->email);
        });

        // Assert email log was created and marked as sent
        $this->assertDatabaseHas('email_logs', [
            'form_link_id' => $formLink->id,
            'email_to' => $formLink->email,
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_logs_failure_on_error(): void
    {
        Mail::shouldReceive('send')->andThrow(new \Exception('SMTP Error'));

        $formLink = FormLink::factory()->create();

        $job = new SendFormLinkEmail($formLink);

        $this->expectException(\Exception::class);

        $job->handle();

        // Assert email log marked as failed
        $this->assertDatabaseHas('email_logs', [
            'form_link_id' => $formLink->id,
            'status' => 'failed',
        ]);
    }
}