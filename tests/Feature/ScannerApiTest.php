<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permissions\MudikPermissions;
use App\Models\Admin;
use App\Models\FormLink;
use App\Models\QrCode;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScannerApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $scanner;

    protected function setUp(): void
    {
        parent::setUp();

        // Create scanner admin with permission
        $this->scanner = Admin::factory()->create([
            'role' => 'scanner',
            'can_scan' => true,
            'is_active' => true,
        ]);

        // Grant permission (assuming Spatie Permission package)
        // $this->scanner->givePermissionTo(MudikPermissions::ScanQR->value);
    }

    /** @test */
    public function it_validates_qr_code_successfully(): void
    {
        $qrCode = $this->createValidQrCode();

        $response = $this->actingAs($this->scanner, 'admin')
            ->getJson('/cms/api/scan/validate?token_qr=' . $qrCode->token_qr);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'QR Code valid',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'qr_code',
                    'registration',
                    'email',
                    'participants',
                    'validation',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_qr_code(): void
    {
        $response = $this->actingAs($this->scanner, 'admin')
            ->getJson('/cms/api/scan/validate?token_qr=non-existent-token');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error_code' => 'QR_NOT_FOUND',
            ]);
    }

    /** @test */
    public function it_returns_422_for_already_scanned_qr_code(): void
    {
        $qrCode = $this->createValidQrCode();
        $qrCode->markAsScanned($this->scanner->id);

        $response = $this->actingAs($this->scanner, 'admin')
            ->getJson('/cms/api/scan/validate?token_qr=' . $qrCode->token_qr);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'QR_ALREADY_SCANNED',
            ])
            ->assertJsonStructure([
                'data' => ['scanned_at', 'scanned_by'],
            ]);
    }

    /** @test */
    public function it_returns_422_for_expired_qr_code(): void
    {
        $qrCode = $this->createValidQrCode();
        $qrCode->update([
            'valid_from' => Carbon::now()->subDays(2),
            'valid_until' => Carbon::now()->subDays(1),
        ]);

        $response = $this->actingAs($this->scanner, 'admin')
            ->getJson('/cms/api/scan/validate?token_qr=' . $qrCode->token_qr);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'QR_INVALID_DATE',
            ]);
    }

    /** @test */
    public function it_consumes_qr_code_successfully(): void
    {
        Event::fake();

        $qrCode = $this->createValidQrCode();

        $response = $this->actingAs($this->scanner, 'admin')
            ->postJson('/cms/api/scan/consume', [
                'token_qr' => $qrCode->token_qr,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'QR Code successfully scanned',
            ])
            ->assertJsonStructure([
                'data' => [
                    'scan_id',
                    'scanned_at',
                    'scanned_by',
                    'registration',
                    'participants',
                ],
            ]);

        // Verify QR code is marked as scanned
        $qrCode->refresh();
        $this->assertTrue($qrCode->isScanned());
        $this->assertEquals($this->scanner->id, $qrCode->scanned_by);

        // Verify scan log
        $this->assertDatabaseHas('scan_logs', [
            'qr_code_id' => $qrCode->id,
            'admin_id' => $this->scanner->id,
            'scan_result' => 'success',
        ]);

        // Verify event broadcast
        Event::assertDispatched(\App\Events\ScanPerformed::class);
    }

    /** @test */
    public function it_prevents_double_scanning(): void
    {
        $qrCode = $this->createValidQrCode();

        // First scan
        $this->actingAs($this->scanner, 'admin')
            ->postJson('/cms/api/scan/consume', [
                'token_qr' => $qrCode->token_qr,
            ])
            ->assertStatus(200);

        // Second scan should fail
        $response = $this->actingAs($this->scanner, 'admin')
            ->postJson('/cms/api/scan/consume', [
                'token_qr' => $qrCode->token_qr,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_code' => 'QR_ALREADY_SCANNED',
            ]);
    }

    /** @test */
    public function it_requires_authentication(): void
    {
        $qrCode = $this->createValidQrCode();

        $response = $this->getJson('/cms/api/scan/validate?token_qr=' . $qrCode->token_qr);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_scan_permission(): void
    {
        // Create admin without scan permission
        $adminWithoutPermission = Admin::factory()->create([
            'role' => 'validator',
            'can_scan' => false,
        ]);

        $qrCode = $this->createValidQrCode();

        $response = $this->actingAs($adminWithoutPermission, 'admin')
            ->getJson('/cms/api/scan/validate?token_qr=' . $qrCode->token_qr);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_validates_token_qr_is_required(): void
    {
        $response = $this->actingAs($this->scanner, 'admin')
            ->getJson('/cms/api/scan/validate');

        $response->assertStatus(400)
            ->assertJson([
                'error_code' => 'TOKEN_REQUIRED',
            ]);
    }

    /** @test */
    public function it_includes_warning_for_children_under_4(): void
    {
        $qrCode = $this->createValidQrCode(hasChildUnder4: true);

        $response = $this->actingAs($this->scanner, 'admin')
            ->postJson('/cms/api/scan/consume', [
                'token_qr' => $qrCode->token_qr,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.warnings.0', 'Anak dibawah 4 tahun wajib dipangku selama perjalanan');
    }

    protected function createValidQrCode(bool $hasChildUnder4 = false): QrCode
    {
        $formLink = FormLink::factory()->create(['status' => 'approved']);
        
        $registration = Registration::factory()->create([
            'form_link_id' => $formLink->id,
            'has_child_under_4' => $hasChildUnder4,
        ]);

        return QrCode::factory()->create([
            'registration_id' => $registration->id,
            'valid_from' => Carbon::now()->subHours(1),
            'valid_until' => Carbon::now()->addHours(23),
            'scanned_at' => null,
        ]);
    }
}