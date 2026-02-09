<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendQrCodeEmail;
use App\Mail\QrCodeMail;
use App\Models\FormLink;
use App\Models\QrCode;
use App\Models\Registration;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendQrCodeEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_qr_code_email_successfully(): void
    {
        Mail::fake();

        $registration = $this->createRegistrationWithQrCode();

        $job = new SendQrCodeEmail($registration);
        $job->handle(app(QrCodeService::class));

        // Assert email was sent
        Mail::assertSent(QrCodeMail::class, function (QrCodeMail $mail) use ($registration) {
            return $mail->hasTo($registration->formLink->email)
                && $mail->registration->id === $registration->id;
        });

        // Assert email log was created and marked as sent
        $this->assertDatabaseHas('email_logs', [
            'form_link_id' => $registration->form_link_id,
            'email_to' => $registration->formLink->email,
            'email_type' => 'qr_code',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_logs_failure_when_email_sending_fails(): void
    {
        Mail::shouldReceive('to')
            ->andReturnSelf()
            ->shouldReceive('send')
            ->andThrow(new \Exception('SMTP Error'));

        $registration = $this->createRegistrationWithQrCode();

        $job = new SendQrCodeEmail($registration);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SMTP Error');

        $job->handle(app(QrCodeService::class));

        // Assert email log was created and marked as failed
        $this->assertDatabaseHas('email_logs', [
            'form_link_id' => $registration->form_link_id,
            'email_to' => $registration->formLink->email,
            'email_type' => 'qr_code',
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_includes_qr_code_data_in_email(): void
    {
        Mail::fake();

        $registration = $this->createRegistrationWithQrCode();
        $qrCode = $registration->qrCode;

        $job = new SendQrCodeEmail($registration);
        $job->handle(app(QrCodeService::class));

        Mail::assertSent(QrCodeMail::class, function (QrCodeMail $mail) use ($qrCode) {
            return $mail->registration->qrCode->token_qr === $qrCode->token_qr
                && !empty($mail->qrCodeImage);
        });
    }

    /** @test */
    public function it_retries_on_failure(): void
    {
        $job = new SendQrCodeEmail(
            $this->createRegistrationWithQrCode()
        );

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->timeout);
    }

    protected function createRegistrationWithQrCode(): Registration
    {
        // Create form link with approved status
        $formLink = FormLink::factory()->create([
            'status' => 'approved'
        ]);
        
        // Create registration (no status column in registrations table)
        $registration = Registration::factory()->create([
            'form_link_id' => $formLink->id,
        ]);

        // Create QR code
        QrCode::factory()->create([
            'registration_id' => $registration->id,
            'valid_from' => Carbon::now()->subHours(1),
            'valid_until' => Carbon::now()->addHours(23),
        ]);

        return $registration->fresh(['qrCode', 'formLink']);
    }
}