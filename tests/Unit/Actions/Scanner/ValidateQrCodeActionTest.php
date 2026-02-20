<?php

namespace Tests\Unit\Actions\Scanner;

use App\Actions\Scanner\ValidateQrCodeAction;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\FormLink;
use App\Models\Participant;
use App\Models\QrCode;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidateQrCodeActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_valid_qr_code_successfully(): void
    {
        $qrCode = $this->createValidQrCode();

        $result = ValidateQrCodeAction::run($qrCode->token_qr);

        $this->assertTrue($result['success']);
        $this->assertEquals('QR Code valid', $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('qr_code', $result['data']);
        $this->assertArrayHasKey('registration', $result['data']);
        $this->assertArrayHasKey('participants', $result['data']);
        $this->assertArrayHasKey('validation', $result['data']);
        
        $validation = $result['data']['validation'];
        $this->assertTrue($validation['is_valid']);
        $this->assertFalse($validation['has_been_scanned']);
    }

    /** @test */
    public function it_throws_exception_for_non_existent_qr_code(): void
    {
        $this->expectException(MudikException::class);
        $this->expectExceptionMessage(ErrorCode::QrNotFound->getMessage());

        ValidateQrCodeAction::run('non-existent-token');
    }

    /** @test */
    public function it_throws_exception_for_already_scanned_qr_code(): void
    {
        $admin = Admin::factory()->create();
        $qrCode = $this->createValidQrCode();
        $qrCode->markAsScanned($admin->id);

        $this->expectException(MudikException::class);

        try {
            ValidateQrCodeAction::run($qrCode->token_qr);
        } catch (MudikException $e) {
            $this->assertEquals(ErrorCode::QrAlreadyScanned, $e->getErrorCode());
            $additionalData = $e->getAdditionalData();
            $this->assertArrayHasKey('scanned_at', $additionalData);
            $this->assertArrayHasKey('scanned_by', $additionalData);
            throw $e;
        }
    }

    /** @test */
    public function it_throws_exception_for_expired_qr_code(): void
    {
        $qrCode = $this->createValidQrCode();
        $qrCode->update([
            'valid_from' => Carbon::now()->subDays(2),
            'valid_until' => Carbon::now()->subDays(1),
        ]);

        $this->expectException(MudikException::class);

        try {
            ValidateQrCodeAction::run($qrCode->token_qr);
        } catch (MudikException $e) {
            $this->assertEquals(ErrorCode::QrInvalidDate, $e->getErrorCode());
            $additionalData = $e->getAdditionalData();
            $this->assertArrayHasKey('valid_from', $additionalData);
            $this->assertArrayHasKey('valid_until', $additionalData);
            $this->assertArrayHasKey('current_time', $additionalData);
            throw $e;
        }
    }

    /** @test */
    public function it_throws_exception_for_not_yet_valid_qr_code(): void
    {
        $qrCode = $this->createValidQrCode();
        $qrCode->update([
            'valid_from' => Carbon::now()->addDays(1),
            'valid_until' => Carbon::now()->addDays(2),
        ]);

        $this->expectException(MudikException::class);

        ValidateQrCodeAction::run($qrCode->token_qr);
    }

    /** @test */
    public function it_includes_all_participants_in_response(): void
    {
        $qrCode = $this->createValidQrCode();
        
        // Add participants
        Participant::factory()->count(3)->create([
            'registration_id' => $qrCode->registration_id,
        ]);

        $result = ValidateQrCodeAction::run($qrCode->token_qr);

        $this->assertCount(3, $result['data']['participants']);
        
        foreach ($result['data']['participants'] as $participant) {
            $this->assertArrayHasKey('full_name', $participant);
            $this->assertArrayHasKey('nik_kia', $participant);
            $this->assertArrayHasKey('age', $participant);
            $this->assertArrayHasKey('is_child_under_4', $participant);
        }
    }

    protected function createValidQrCode(): QrCode
    {
        $formLink = FormLink::factory()->create(['status' => 'approved']);
        
        $registration = Registration::factory()->create([
            'form_link_id' => $formLink->id,
        ]);

        return QrCode::factory()->create([
            'registration_id' => $registration->id,
            'valid_from' => Carbon::now()->subHours(1),
            'valid_until' => Carbon::now()->addHours(23),
            'scanned_at' => null,
        ]);
    }
}