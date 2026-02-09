<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendRejectionEmail;
use App\Mail\RejectionMail;
use App\Models\FormLink;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendRejectionEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_rejection_email_successfully(): void
    {
        Mail::fake();

        $registration = $this->createRejectedRegistration();

        $job = new SendRejectionEmail($registration);
        $job->handle();

        // Assert email was sent
        Mail::assertSent(RejectionMail::class, function (RejectionMail $mail) use ($registration) {
            return $mail->hasTo($registration->formLink->email)
                && $mail->registration->id === $registration->id;
        });

        // Assert email log was created and marked as sent
        $this->assertDatabaseHas('email_logs', [
            'form_link_id' => $registration->form_link_id,
            'email_to' => $registration->formLink->email,
            'email_type' => 'rejection',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_logs_failure_when_email_sending_fails(): void
    {
        Mail::shouldReceive('to')
            ->andReturnSelf()
            ->shouldReceive('send')
            ->andThrow(new \Exception('Mail server unavailable'));

        $registration = $this->createRejectedRegistration();

        $job = new SendRejectionEmail($registration);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Mail server unavailable');

        $job->handle();

        // Assert email log was created and marked as failed
        $this->assertDatabaseHas('email_logs', [
            'form_link_id' => $registration->form_link_id,
            'email_to' => $registration->formLink->email,
            'email_type' => 'rejection',
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_includes_rejection_reason_in_email(): void
    {
        Mail::fake();

        $rejectionReason = 'Data tidak lengkap atau tidak valid';
        $registration = $this->createRejectedRegistration($rejectionReason);

        $job = new SendRejectionEmail($registration);
        $job->handle();

        Mail::assertSent(RejectionMail::class, function (RejectionMail $mail) use ($rejectionReason) {
            return $mail->rejectionReason === $rejectionReason;
        });
    }

    /** @test */
    public function it_uses_default_reason_when_no_rejection_reason_provided(): void
    {
        Mail::fake();

        $registration = $this->createRejectedRegistration(null);

        $job = new SendRejectionEmail($registration);
        $job->handle();

        Mail::assertSent(RejectionMail::class, function (RejectionMail $mail) {
            return $mail->rejectionReason === 'Tidak ada alasan yang diberikan.';
        });
    }

    /** @test */
    public function it_includes_website_url_in_email(): void
    {
        Mail::fake();

        $registration = $this->createRejectedRegistration();

        $job = new SendRejectionEmail($registration);
        $job->handle();

        Mail::assertSent(RejectionMail::class, function (RejectionMail $mail) {
            return !empty($mail->websiteUrl);
        });
    }

    /** @test */
    public function it_retries_on_failure(): void
    {
        $job = new SendRejectionEmail(
            $this->createRejectedRegistration()
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    protected function createRejectedRegistration(?string $rejectionReason = 'Data tidak valid'): Registration
    {
        // Create form link with rejected status
        $formLink = FormLink::factory()->create([
            'status' => 'rejected'
        ]);
        
        // Create registration with rejection data
        return Registration::factory()->create([
            'form_link_id' => $formLink->id,
            'rejection_reason' => $rejectionReason,
            'rejected_at' => now(),
        ]);
    }
}