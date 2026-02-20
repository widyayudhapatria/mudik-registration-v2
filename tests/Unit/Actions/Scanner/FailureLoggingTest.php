<?php

namespace Tests\Unit\Actions\Scanner;

use App\Actions\Scanner\ConsumeQrCodeAction;
use App\Actions\Scanner\ValidateQrCodeAction;
use App\Data\ScanQrData;
use App\Enums\ErrorCode;
use App\Exceptions\MudikException;
use App\Models\Admin;
use App\Models\Destination;
use App\Models\FormLink;
use App\Models\QrCode;
use App\Models\Registration;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FailureLoggingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_failure_when_qr_already_scanned(): void
    {
        $admin = Admin::factory()->create(['can_scan' => true]);
        $destination = Destination::create(['code' => 'JKT', 'name' => 'Jakarta', 'total_quota' => 1000, 'used_quota' => 0, 'is_active' => true]);
        $formLink = FormLink::factory()->create(['status' => 'approved']);

        $registration = Registration::factory()->create([
            'form_link_id' => $formLink->id,
            'destination_id' => $destination->id,
        ]);

        $qrCode = QrCode::factory()->create([
            'registration_id' => $registration->id,
            'valid_from' => Carbon::now()->subHours(1),
            'valid_until' => Carbon::now()->addHours(23),
        ]);

        // First scan - should succeed
        $data = new ScanQrData(token_qr: $qrCode->token_qr);
        $result = ConsumeQrCodeAction::run($data, $admin);
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('scan_logs', [
            'qr_code_id' => $qrCode->id,
            'scan_result' => 'success',
        ]);

        // Second scan - should fail and create a failure log
        $data2 = new ScanQrData(token_qr: $qrCode->token_qr);
        try {
            ConsumeQrCodeAction::run($data2, $admin);
            $this->fail('Expected exception was not thrown');
        } catch (MudikException $e) {
            $this->assertEquals(ErrorCode::QrAlreadyScanned, $e->getErrorCode());
        }

        // Verify failure log was created with detailed reason
        $failureLog = ScanLog::where('qr_code_id', $qrCode->id)
            ->where('scan_result', 'failed')
            ->first();

        $this->assertNotNull($failureLog, 'Failed scan log should exist');
        $this->assertNotNull($failureLog->failure_reason);
        $this->assertStringContainsString('Already scanned at', $failureLog->failure_reason);
    }

    /** @test */
    public function it_logs_failure_when_qr_not_found(): void
    {
        $admin = Admin::factory()->create(['can_scan' => true]);
        $data = new ScanQrData(token_qr: 'non-existent-token-12345');

        try {
            ConsumeQrCodeAction::run($data, $admin);
            $this->fail('Expected exception was not thrown');
        } catch (MudikException $e) {
            $this->assertEquals(ErrorCode::QrNotFound, $e->getErrorCode());
        }

        // Verify failure log was created with null qr_code_id
        $failureLog = ScanLog::where('qr_code_id', null)
            ->where('scan_result', 'failed')
            ->first();

        $this->assertNotNull($failureLog, 'Failed scan log for not-found QR should exist');
        $this->assertNull($failureLog->qr_code_id);
        $this->assertStringContainsString('QR not found', $failureLog->failure_reason);
        $this->assertStringContainsString('non-existent-token-12345', $failureLog->failure_reason);
    }

    /** @test */
    public function it_logs_failure_when_qr_date_invalid(): void
    {
        $admin = Admin::factory()->create(['can_scan' => true]);
        $destination = Destination::create(['code' => 'JKT', 'name' => 'Jakarta', 'total_quota' => 1000, 'used_quota' => 0, 'is_active' => true]);
        $formLink = FormLink::factory()->create(['status' => 'approved']);

        $registration = Registration::factory()->create([
            'form_link_id' => $formLink->id,
            'destination_id' => $destination->id,
        ]);

        $qrCode = QrCode::factory()->create([
            'registration_id' => $registration->id,
            'valid_from' => Carbon::now()->addDays(1),
            'valid_until' => Carbon::now()->addDays(2),
        ]);

        $data = new ScanQrData(token_qr: $qrCode->token_qr);

        try {
            ConsumeQrCodeAction::run($data, $admin);
            $this->fail('Expected exception was not thrown');
        } catch (MudikException $e) {
            $this->assertEquals(ErrorCode::QrInvalidDate, $e->getErrorCode());
        }

        // Verify failure log was created
        $failureLog = ScanLog::where('qr_code_id', $qrCode->id)
            ->where('scan_result', 'failed')
            ->first();

        $this->assertNotNull($failureLog, 'Failed scan log should exist');
        $this->assertStringContainsString('not yet valid', $failureLog->failure_reason);
    }

    /** @test */
    public function it_logs_failure_from_validate_action_when_qr_already_scanned(): void
    {
        $admin = Admin::factory()->create(['can_scan' => true]);
        $destination = Destination::create(['code' => 'JKT', 'name' => 'Jakarta', 'total_quota' => 1000, 'used_quota' => 0, 'is_active' => true]);
        $formLink = FormLink::factory()->create(['status' => 'approved']);

        $registration = Registration::factory()->create([
            'form_link_id' => $formLink->id,
            'destination_id' => $destination->id,
        ]);

        $qrCode = QrCode::factory()->create([
            'registration_id' => $registration->id,
            'valid_from' => Carbon::now()->subHours(1),
            'valid_until' => Carbon::now()->addHours(23),
        ]);

        // First scan - should succeed
        $data = new ScanQrData(token_qr: $qrCode->token_qr);
        $result = ConsumeQrCodeAction::run($data, $admin);
        $this->assertTrue($result['success']);

        // Second validation - should fail and create a failure log (simulating manual input flow)
        $this->app->instance('request', app('request')->setUserResolver(fn() => $admin));

        try {
            ValidateQrCodeAction::run($qrCode->token_qr, $admin);
            $this->fail('Expected exception was not thrown');
        } catch (MudikException $e) {
            $this->assertEquals(ErrorCode::QrAlreadyScanned, $e->getErrorCode());
        }

        // Verify failure log was created from validate action
        $failureLog = ScanLog::where('qr_code_id', $qrCode->id)
            ->where('scan_result', 'failed')
            ->first();

        $this->assertNotNull($failureLog, 'Failed scan log from validate should exist');
        $this->assertStringContainsString('Already scanned at', $failureLog->failure_reason);
    }
}
